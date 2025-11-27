@props([
    'type' => 'success', 
    'message' => session($type), // default ambil dari session
    'position' => 'top-right', // top-right, top-left, bottom-right, bottom-left
    'duration' => 4000 // durasi dalam ms
])

@php
    // Konfigurasi warna dan ikon berdasarkan type
    $config = [
        'success' => [
            'bg' => 'bg-gradient-to-r from-green-500 to-green-600',
            'border' => 'border-green-400',
            'icon' => 'M9 12l2 2l4-4',
            'iconBg' => 'bg-green-400'
        ],
        'error' => [
            'bg' => 'bg-gradient-to-r from-red-500 to-red-600',
            'border' => 'border-red-400',
            'icon' => 'M6 18L18 6M6 6l12 12',
            'iconBg' => 'bg-red-400'
        ],
        'warning' => [
            'bg' => 'bg-gradient-to-r from-yellow-500 to-yellow-600',
            'border' => 'border-yellow-400',
            'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
            'iconBg' => 'bg-yellow-400'
        ],
        'info' => [
            'bg' => 'bg-gradient-to-r from-blue-500 to-blue-600',
            'border' => 'border-blue-400',
            'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'iconBg' => 'bg-blue-400'
        ]
    ];
    
    $currentConfig = $config[$type] ?? $config['success'];
    
    // Konfigurasi posisi
    $positions = [
        'top-right' => 'top-4 right-4',
        'top-left' => 'top-4 left-4',
        'bottom-right' => 'bottom-4 right-4',
        'bottom-left' => 'bottom-4 left-4',
        'top-center' => 'top-4 left-1/2 transform -translate-x-1/2',
        'bottom-center' => 'bottom-4 left-1/2 transform -translate-x-1/2'
    ];
    
    $positionClass = $positions[$position] ?? $positions['top-right'];
@endphp

<div 
    x-data="{
        show: false,
        message: @js($message),
        progress: 100,
        duration: @js($duration),
        timer: null,
        progressTimer: null,
        
        init() {
            if (this.message) {
                // Show notification
                setTimeout(() => this.show = true, 100);
                
                // Start progress bar
                this.startProgress();
                
                // Auto hide
                this.timer = setTimeout(() => {
                    this.hide();
                }, this.duration);
            }
        },
        
        startProgress() {
            const interval = 50; // Update setiap 50ms
            const steps = this.duration / interval;
            const decrement = 100 / steps;
            
            this.progressTimer = setInterval(() => {
                this.progress -= decrement;
                if (this.progress <= 0) {
                    this.progress = 0;
                    clearInterval(this.progressTimer);
                }
            }, interval);
        },
        
        hide() {
            this.show = false;
            if (this.timer) clearTimeout(this.timer);
            if (this.progressTimer) clearInterval(this.progressTimer);
        },
        
        pauseProgress() {
            if (this.timer) clearTimeout(this.timer);
            if (this.progressTimer) clearInterval(this.progressTimer);
        },
        
        resumeProgress() {
            if (this.progress > 0) {
                const remainingTime = (this.progress / 100) * this.duration;
                this.startProgress();
                this.timer = setTimeout(() => this.hide(), remainingTime);
            }
        }
    }" 
    class="relative"
>
    <template x-if="message">
        <div 
            x-show="show"
            @mouseenter="pauseProgress()"
            @mouseleave="resumeProgress()"
            x-transition:enter="transform transition ease-out duration-500"
            x-transition:enter-start="translate-x-full opacity-0 scale-95"
            x-transition:enter-end="translate-x-0 opacity-100 scale-100"
            x-transition:leave="transform transition ease-in duration-300"
            x-transition:leave-start="translate-x-0 opacity-100 scale-100"
            x-transition:leave-end="translate-x-full opacity-0 scale-95"
            x-cloak
            class="fixed {{ $positionClass }} {{ $currentConfig['bg'] }} text-white rounded-lg shadow-2xl z-50 min-w-80 max-w-md overflow-hidden border-l-4 {{ $currentConfig['border'] }}"
        >
            <!-- Main Content -->
            <div class="flex items-center p-4 relative">
                <!-- Icon -->
                <div class="flex-shrink-0 w-8 h-8 {{ $currentConfig['iconBg'] }} rounded-full flex items-center justify-center mr-3 bg-opacity-20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $currentConfig['icon'] }}" />
                    </svg>
                </div>
                
                <!-- Message -->
                <div class="flex-1 mr-2">
                    <p class="text-sm font-medium leading-tight" x-text="message"></p>
                </div>
                
                <!-- Close Button -->
                <button 
                    @click="hide()"
                    class="flex-shrink-0 p-1 hover:bg-white hover:bg-opacity-20 rounded-full transition-colors duration-200"
                >
                    <svg class="w-4 h-4 text-white hover:text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Progress Bar -->
            <div class="h-1 bg-black bg-opacity-20">
                <div 
                    class="h-full bg-white bg-opacity-30 transition-all duration-50 ease-linear"
                    :style="`width: ${progress}%`"
                ></div>
            </div>
        </div>
    </template>
</div>