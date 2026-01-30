<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingController extends Controller
{
    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        $apiKey = env('GOOGLE_MAPS_API_KEY');

        if (!$apiKey) {
            Log::error('Google Maps API Key is not configured.');
            return response()->json(['error' => 'API key not configured on server.'], 500);
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => "{$lat},{$lng}",
                'key' => $apiKey,
                'result_type' => 'street_address|route|neighborhood|political|locality|administrative_area_level_1', // Request more types
                'language' => 'en',
            ]);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['results'][0])) {
                $firstResult = $responseData['results'][0];
                $formattedAddress = $firstResult['formatted_address'];

                // Let's try to get a more specific name, but fallback to formatted_address if needed
                $specificPlaceName = $this->extractSpecificLocationName($firstResult);

                Log::info('Reverse geocode success:', ['address' => $formattedAddress, 'specific_name' => $specificPlaceName]);
                return response()->json([
                    'formatted_address' => $formattedAddress, // The full, most precise address Google gives
                    'place_name' => $specificPlaceName,      // A potentially shorter or more specific component
                    'coordinates' => ['lat' => $lat, 'lng' => $lng] // Send back the coords for display too
                ]);
            } else {
                Log::warning('Google Geocoding API call not successful or no results.', [ /* ... */ ]);
                return response()->json([ /* ... error details ... */ ], 422);
            }
        } catch (\Throwable $e) {
            Log::error('Exception during reverse geocoding: ' . $e->getMessage(), [ /* ... */ ]);
            return response()->json(['error' => 'An unexpected server error occurred during geocoding.'], 500);
        }
    }

    /**
     * Attempts to extract a more specific or relevant place name from address components.
     * Tries for premise, street address, neighborhood, then locality.
     */
    private function extractSpecificLocationName(array $googleResult): string
    {
        $addressComponents = $googleResult['address_components'] ?? [];
        $typesPriority = [
            'premise',                 // Specific building name or point
            'street_address',          // Exact street address
            'point_of_interest',       // POIs
            'establishment',           // Businesses
            'neighborhood',            // Neighborhood
            'sublocality_level_1',     // More specific locality
            'locality',                // City/Town (e.g., "Nairobi")
            'administrative_area_level_1', // State/Province/County
        ];

        foreach ($typesPriority as $type) {
            foreach ($addressComponents as $component) {
                if (in_array($type, $component['types'])) {
                    // For some types, we might want more context than just the name
                    if ($type === 'street_address') {
                        // Often, street_address itself is just the number.
                        // We might want to combine it with 'route' if available.
                        $streetNumber = '';
                        $routeName = '';
                        foreach($addressComponents as $comp){
                            if(in_array('street_number', $comp['types'])) $streetNumber = $comp['long_name'];
                            if(in_array('route', $comp['types'])) $routeName = $comp['long_name'];
                        }
                        if ($streetNumber && $routeName) return $streetNumber . ' ' . $routeName;
                        if ($routeName) return $routeName; // Fallback to just route if no number
                    }
                    return $component['long_name']; // Return the first most specific component found
                }
            }
        }
        // Fallback to the full formatted address if no specific component is suitable
        return $googleResult['formatted_address'] ?? 'Address not found';
    }
}