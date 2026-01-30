@props(['name', 'label', 'options', 'selected' => []])

<div x-data="{ 
    open: false, 
    search: '',
    selected: {{ json_encode(array_map('intval', $selected)) }},
    options: {{ json_encode($options->map(fn($o) => ['id' => $o->id, 'name' => $o->name])) }},
    toggle(id) {
        id = parseInt(id);
        if (this.selected.includes(id)) {
            this.selected = this.selected.filter(i => i !== id);
        } else {
            this.selected.push(id);
        }
    },
    get selectedOptions() {
        return this.options.filter(o => this.selected.includes(parseInt(o.id)));
    },
    get filteredOptions() {
        if (!this.search) return this.options;
        return this.options.filter(o => o.name.toLowerCase().includes(this.search.toLowerCase()));
    }
}" class="w-full">
    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $label }}</label>
    
    <div class="relative">
        <div @click="open = !open" 
             class="min-h-[45px] p-1.5 gap-2 flex flex-wrap items-center bg-white border border-gray-300 rounded-xl cursor-pointer hover:border-blue-400 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition-all shadow-sm">
            
            <template x-for="option in selectedOptions" :key="option.id">
                <span class="inline-flex items-center px-2 py-0.5 rounded border border-blue-200 bg-blue-50 text-blue-700 text-sm group transition-all">
                    <span x-text="option.name"></span>
                    <span class="mx-1 text-blue-200">|</span>
                    <button type="button" @click.stop="toggle(option.id)" class="text-blue-400 hover:text-blue-600 focus:outline-none transition-colors">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <input type="hidden" name="{{ $name }}" :value="option.id">
                </span>
            </template>
            
            <span x-show="selected.length === 0" class="text-gray-400 text-sm ml-2">Click to select...</span>
            
            <div class="flex-1 min-w-[60px]">
                <input type="text" 
                       x-model="search" 
                       @focus="open = true"
                       @click.stop="open = true"
                       placeholder="{{ count($selected) > 0 ? '' : 'Type to search...' }}"
                       class="w-full border-none focus:ring-0 text-sm p-0 bg-transparent">
            </div>

            <!-- Chevron Icon -->
            <div class="pr-2 text-gray-400">
                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <!-- Dropdown -->
        <div x-show="open" 
             @click.away="open = false" 
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="absolute top-full left-0 right-0 z-50 mt-2 max-h-72 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-xl outline-none py-1">
            
            <template x-for="option in filteredOptions" :key="option.id">
                <div @click.stop="toggle(option.id)" 
                     class="px-4 py-2.5 text-sm cursor-pointer transition-colors flex justify-between items-center group"
                     :class="selected.includes(parseInt(option.id)) ? 'bg-blue-50 text-blue-700 font-bold' : 'text-gray-700 hover:bg-gray-50'">
                    <span x-text="option.name"></span>
                    <div class="flex items-center">
                         <svg x-show="selected.includes(parseInt(option.id))" class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="!selected.includes(parseInt(option.id))" class="h-4 w-4 text-gray-300 opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                </div>
            </template>

            <div x-show="filteredOptions.length === 0" class="p-8 text-center">
                <p class="text-sm text-gray-500 italic">No options found for "<span x-text="search"></span>"</p>
            </div>
        </div>
    </div>
</div>
