<?php

namespace App\Http\Controllers\BusinessOwner;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Category;
use App\Models\County;
use App\Models\Facility;
use App\Models\Tag;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;
use Intervention\Image\Typography\FontFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media; // <<< ADDED: Import the Spatie Media model

class BusinessController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Business::class, 'business');
    }

    public function index()
    {
        return redirect()->route('business-owner.dashboard');
    }

	    private function getScheduleValidationRules(Request $request): array
    {
        $scheduleRules = [];
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($daysOfWeek as $day) {
            $isClosedPath = "schedule.{$day}.is_closed_all_day";
            $openTimePath = "schedule.{$day}.open_time";
            $closeTimePath = "schedule.{$day}.close_time";

            $isClosedCurrentRequest = $request->input($isClosedPath) == '1';

            $scheduleRules[$openTimePath]  = [
                'nullable',
                Rule::requiredIf(!$isClosedCurrentRequest),
                'date_format:H:i'
            ];
            $scheduleRules[$closeTimePath] = [
                'nullable',
                Rule::requiredIf(!$isClosedCurrentRequest),
                'date_format:H:i',
                Rule::when($request->filled($openTimePath) && !$isClosedCurrentRequest, [
                    'after_or_equal:'.$openTimePath
                ])
            ];
            $scheduleRules["schedule.{$day}.is_closed_all_day"] = 'nullable|boolean';
            $scheduleRules["schedule.{$day}.notes"] = 'nullable|string|max:100';
        }
        return $scheduleRules;
    }

    public function create()
    {
        $counties = County::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $facilities = Facility::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $defaultSchedulesData = [];
        foreach ($daysOfWeek as $day) {
            if (in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday'])) {
                $defaultSchedulesData[$day] = ['open_time' => '08:00', 'close_time' => '22:00', 'is_closed_all_day' => false, 'notes' => ''];
            } else {
                $defaultSchedulesData[$day] = ['open_time' => '08:00', 'close_time' => '23:59', 'is_closed_all_day' => false, 'notes' => ''];
            }
        }

        return view('business-owner.businesses.create', compact(
            'counties', 'categories', 'facilities', 'tags', 'daysOfWeek', 'defaultSchedulesData'
        ));
    }

    public function store(Request $request)
    {
        $scheduleInput = $request->input('schedule', []);
        foreach ($scheduleInput as $day => &$times) {
            $times['is_closed_all_day'] = isset($times['is_closed_all_day']) && $times['is_closed_all_day'] == '1';
            if ($times['is_closed_all_day']) { $times['open_time'] = null; $times['close_time'] = null; }
            elseif (empty($times['open_time']) && empty($times['close_time'])) { $times['open_time'] = null; $times['close_time'] = null; }
        }
        unset($times);
        $request->merge(['schedule' => $scheduleInput]);
        $scheduleRules = $this->getScheduleValidationRules($request);
        $validatedData = $request->validate(array_merge([
            'name' => 'required|string|max:255', 'about_us' => 'required|string|min:20', 'description' => 'nullable|string',
            'address' => 'required|string|max:255', 'county_id' => 'required|exists:counties,id',
            'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180',
            'phone_number' => 'nullable|string|max:20', 'email' => ['nullable', 'email', 'max:255', Rule::unique('businesses', 'email')],
            'website' => 'nullable|url|max:255', 'min_price' => 'nullable|numeric|min:0|lte:max_price',
            'max_price' => 'nullable|numeric|min:0|gte:min_price', 'price_range' => 'nullable|string|max:50',
            'categories' => 'required|array|min:1', 'categories.*' => 'exists:categories,id',
            'facilities' => 'nullable|array', 'facilities.*' => 'exists:facilities,id',
            'tags' => 'nullable|array', 'tags.*' => 'exists:tags,id',
            'social_links' => 'nullable|array', 'social_links.*' => 'nullable|url|max:255',
            'images' => 'nullable|array|max:10', 'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'new_main_image_index' => 'nullable|integer|min:0',
        ], $scheduleRules));

        $slug = Str::slug($validatedData['name']);
        $originalSlug = $slug; $count = 1;
        while (Business::where('slug', $slug)->exists()) { $slug = $originalSlug . '-' . $count++; }
        $businessData = $validatedData;
        unset($businessData['categories'], $businessData['facilities'], $businessData['tags'], $businessData['schedule'], $businessData['images'], $businessData['new_main_image_index']);
        $businessData['user_id'] = Auth::id();
        $businessData['slug'] = $slug;
        $businessData['status'] = 'pending_approval';
        $businessData['social_links'] = array_filter($request->input('social_links', []));
        $business = Business::create($businessData);
        $business->categories()->attach($validatedData['categories']);
        if ($request->has('facilities')) $business->facilities()->attach($request->input('facilities'));
        if ($request->has('tags')) $business->tags()->attach($request->input('tags'));
        if ($request->has('schedule')) {
            foreach ($request->input('schedule') as $day => $times) {
                $isClosed = $times['is_closed_all_day'] ?? false;
                if ($isClosed || (!$isClosed && !empty($times['open_time']) && !empty($times['close_time']))) {
                    Schedule::create(['business_id' => $business->id, 'day_of_week' => $day, 'open_time' => $isClosed ? null : ($times['open_time'] ?? null), 'close_time' => $isClosed ? null : ($times['close_time'] ?? null), 'is_closed_all_day' => $isClosed, 'notes' => $times['notes'] ?? null]);
                }
            }
        }

        if ($request->hasFile('images')) {
            $newlyUploadedMedia = [];
            $countyName = $business->county->name ?? '';
            foreach ($request->file('images') as $imageFile) {
                $filename = Str::slug($business->name . '-' . $countyName . '-' . uniqid()) . '.' . $imageFile->getClientOriginalExtension();
                $media = $business->addMedia($imageFile)
                    ->usingFileName($filename)
                    ->toMediaCollection('images');
                $newlyUploadedMedia[] = $media;
            }

            $mainImageToSet = null;
            if ($request->filled('new_main_image_index') && isset($newlyUploadedMedia[(int)$request->input('new_main_image_index')])) {
                $mainImageToSet = $newlyUploadedMedia[(int)$request->input('new_main_image_index')];
            } 
            elseif (!empty($newlyUploadedMedia)) {
                $mainImageToSet = $newlyUploadedMedia[0];
            }

            if ($mainImageToSet) {
                $this->setMainImage($business, $mainImageToSet->id);
            }
        }

        return redirect()->route('business-owner.dashboard')->with('success', 'Business listing submitted!');
    }

    public function edit(Business $business)
    {
        $counties = County::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $facilities = Facility::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $business->load('categories', 'facilities', 'tags', 'schedules');
        $schedulesData = [];
       foreach ($daysOfWeek as $day) {
        $schedule = $business->schedules->firstWhere('day_of_week', $day);
        $schedulesData[$day] = [
            'open_time' => $schedule && $schedule->open_time ? \Carbon\Carbon::parse($schedule->open_time)->format('H:i') : '',
            'close_time' => $schedule && $schedule->close_time ? \Carbon\Carbon::parse($schedule->close_time)->format('H:i') : '',
            'is_closed_all_day' => $schedule->is_closed_all_day ?? false,
            'notes' => $schedule->notes ?? '',
        ];
    }

        return view('business-owner.businesses.edit', compact('business', 'counties', 'categories', 'facilities', 'tags', 'schedulesData', 'daysOfWeek'));
    }

    public function update(Request $request, Business $business)
    {
        $scheduleInput = $request->input('schedule', []);
        foreach ($scheduleInput as $day => &$times) {
            $times['is_closed_all_day'] = isset($times['is_closed_all_day']) && $times['is_closed_all_day'] == '1';
            if ($times['is_closed_all_day']) {
                $times['open_time'] = null; $times['close_time'] = null;
            } elseif (empty($times['open_time']) && empty($times['close_time'])) {
                $times['open_time'] = null; $times['close_time'] = null;
            }
        }
        unset($times);
        $request->merge(['schedule' => $scheduleInput]);
        $scheduleRules = $this->getScheduleValidationRules($request);
        $validatedData = $request->validate(array_merge([
            'name' => 'required|string|max:255', 'about_us' => 'required|string|min:20', 'description' => 'nullable|string',
            'address' => 'required|string|max:255', 'county_id' => 'required|exists:counties,id',
            'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180',
            'phone_number' => 'nullable|string|max:20', 'email' => ['nullable', 'email', 'max:255', Rule::unique('businesses', 'email')->ignore($business->id)],
            'website' => 'nullable|url|max:255', 'min_price' => 'nullable|numeric|min:0|lte:max_price', 'max_price' => 'nullable|numeric|min:0|gte:min_price', 'price_range' => 'nullable|string|max:50',
            'categories' => 'required|array|min:1', 'categories.*' => 'exists:categories,id',
            'facilities' => 'nullable|array', 'facilities.*' => 'exists:facilities,id',
            'tags' => 'nullable|array', 'tags.*' => 'exists:tags,id',
            'social_links' => 'nullable|array', 'social_links.*' => 'nullable|url|max:255',
            'images' => 'nullable|array|max:10', 'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'main_image_id' => 'nullable|integer|exists:media,id,model_id,'.$business->id,
            'new_main_image_index' => 'nullable|integer|min:0',
            'delete_images' => 'nullable|array', 'delete_images.*' => 'integer|exists:media,id,model_id,'.$business->id,
        ], $scheduleRules));

        $businessUpdateData = $validatedData;
        unset($businessUpdateData['categories'], $businessUpdateData['facilities'], $businessUpdateData['tags'], $businessUpdateData['schedule'], $businessUpdateData['images'], $businessUpdateData['new_main_image_index'], $businessUpdateData['main_image_id'], $businessUpdateData['delete_images']);
        $businessUpdateData['social_links'] = array_filter($request->input('social_links', []));
         if ($business->name !== $validatedData['name']) {
            $slug = Str::slug($validatedData['name']); $originalSlug = $slug; $count = 1;
            while (Business::where('slug', $slug)->where('id', '!=', $business->id)->exists()) { $slug = $originalSlug.'-'.$count++; }
            $businessUpdateData['slug'] = $slug;
        }
        $business->update($businessUpdateData);
        $business->categories()->sync($request->input('categories', []));
        $business->facilities()->sync($request->input('facilities', []));
        $business->tags()->sync($request->input('tags', []));
        $business->schedules()->delete();
          if ($request->has('schedule')) {
            foreach ($request->input('schedule') as $day => $times) {
                $isClosed = $times['is_closed_all_day'] ?? false;
                if ($isClosed || (!$isClosed && !empty($times['open_time']) && !empty($times['close_time']))) {
                    Schedule::create([
                        'business_id' => $business->id,
                        'day_of_week' => $day,
                        'open_time' => $isClosed ? null : ($times['open_time'] ?? null),
                        'close_time' => $isClosed ? null : ($times['close_time'] ?? null),
                        'is_closed_all_day' => $isClosed,
                        'notes' => $times['notes'] ?? null,
                    ]);
                }
            }
        }

        if ($request->has('delete_images')) {
            $business->getMedia('images')
                ->whereIn('id', $request->input('delete_images'))
                ->each(fn ($media) => $media->delete());
        }

        $newlyUploadedMedia = [];
        if ($request->hasFile('images')) {
            $countyName = $business->county->name ?? '';
            foreach ($request->file('images') as $imageFile) {
                $filename = Str::slug($business->name . '-' . $countyName . '-' . uniqid()) . '.' . $imageFile->getClientOriginalExtension();
                $media = $business->addMedia($imageFile)
                    ->usingFileName($filename)
                    ->toMediaCollection('images');
                $newlyUploadedMedia[] = $media;
            }
        }

        $mainMediaId = null;
        if ($request->filled('new_main_image_index') && isset($newlyUploadedMedia[(int)$request->input('new_main_image_index')])) {
            $mainMediaId = $newlyUploadedMedia[(int)$request->input('new_main_image_index')]->id;
        } elseif ($request->filled('main_image_id')) {
            $mainMediaId = $request->input('main_image_id');
        }

        if ($mainMediaId) {
            $this->setMainImage($business, $mainMediaId);
        } elseif ($business->getMedia('images')->count() > 0 && !$business->getFirstMedia('images')) {
            $this->setMainImage($business, $business->getMedia('images')->first()->id);
        }

        return redirect()->route('business-owner.dashboard')->with('success', 'Business listing updated!');
    }

    // <<< CHANGED: This entire method is replaced with a more robust version that updates BOTH systems. >>>
    private function setMainImage(Business $business, int $mainMediaId): void
    {
        $allMedia = $business->getMedia('images');
        $mediaToSetAsMain = $allMedia->find($mainMediaId);

        if (!$mediaToSetAsMain) {
            return; // Exit if the media doesn't exist
        }

        // The Spatie Media Library handles 'main' by order.
        $newOrderIds = $allMedia->reject(fn ($item) => $item->id === $mainMediaId)
                               ->pluck('id')
                               ->prepend($mainMediaId)
                               ->toArray();
        Media::setNewOrder($newOrderIds);

    }

    public function destroy(Business $business)
    {
        $business->delete(); 
        
        return redirect()->route('business-owner.dashboard')->with('success', 'Business listing removed.');
    }
}