/**
 * TTSButtonController - Manages TTS button state and behavior
 * Handles debouncing, processing state, and UI updates
 * Requirements: 1.2, 1.4, 1.5, 3.1, 3.2, 3.3
 */
class TTSButtonController {
    /**
     * Create a TTSButtonController
     * @param {HTMLButtonElement} buttonElement - The convert button element
     * @param {HTMLAudioElement} audioElement - The audio player element
     */
    constructor(buttonElement, audioElement) {
        if (!buttonElement) {
            throw new Error('Button element is required');
        }
        
        this.button = buttonElement;
        this.audio = audioElement;
        this.isProcessing = false;
        this.lastClickTime = 0;
        this.debounceDelay = 500; // 500ms debounce as specified in design
        
        // Store original button content for restoration
        this.originalContent = this.button.innerHTML;
        this.originalDisabled = this.button.disabled;
        
        console.log('[TTSButtonController] Initialized with debounce delay:', this.debounceDelay + 'ms');
    }
    
    /**
     * Check if a new request can be processed
     * Validates both processing state and debounce timing
     * Requirements: 1.2, 3.1
     * @returns {boolean} True if request can be processed
     */
    canProcess() {
        const now = Date.now();
        const timeSinceLastClick = now - this.lastClickTime;
        
        // Cannot process if already processing or within debounce delay
        const canProcess = !this.isProcessing && timeSinceLastClick >= this.debounceDelay;
        
        if (!canProcess) {
            console.log('[TTSButtonController] Request blocked - processing:', this.isProcessing, 'timeSince:', timeSinceLastClick + 'ms');
        }
        
        return canProcess;
    }
    
    /**
     * Set the processing state and update UI accordingly
     * Requirements: 1.2, 1.4, 3.1, 3.2
     * @param {boolean} processing - Whether processing is active
     */
    setProcessing(processing) {
        if (typeof processing !== 'boolean') {
            throw new Error('Processing state must be a boolean');
        }
        
        console.log('[TTSButtonController] Setting processing state:', processing);
        
        this.isProcessing = processing;
        
        // Update last click time when starting processing
        if (processing) {
            this.lastClickTime = Date.now();
        }
        
        this.updateButtonState();
    }
    
    /**
     * Update button visual state based on processing status
     * Handles disabled state, text content, and styling
     * Requirements: 1.4, 3.2, 3.3
     */
    updateButtonState() {
        if (!this.button) {
            console.warn('[TTSButtonController] Button element not available for state update');
            return;
        }
        
        if (this.isProcessing) {
            // Set processing state - Requirements 1.2, 3.1, 3.2
            this.button.disabled = true;
            this.button.innerHTML = 'Đang xử lý...';
            
            // Add processing class if classList is available
            if (this.button.classList) {
                this.button.classList.add('processing');
            }
            
            console.log('[TTSButtonController] Button set to processing state');
        } else {
            // Restore original state - Requirements 1.4, 1.5, 3.3
            this.button.disabled = this.originalDisabled;
            this.button.innerHTML = this.originalContent;
            
            // Remove processing class if classList is available
            if (this.button.classList) {
                this.button.classList.remove('processing');
            }
            
            console.log('[TTSButtonController] Button restored to original state');
        }
    }
    
    /**
     * Reset the controller to initial state
     * Useful for cleanup or error recovery
     * Requirements: 1.4, 1.5
     */
    reset() {
        console.log('[TTSButtonController] Resetting controller state');
        this.isProcessing = false;
        this.lastClickTime = 0;
        this.updateButtonState();
    }
    
    /**
     * Get current state information for debugging
     * @returns {Object} Current state object
     */
    getState() {
        return {
            isProcessing: this.isProcessing,
            lastClickTime: this.lastClickTime,
            debounceDelay: this.debounceDelay,
            canProcess: this.canProcess(),
            timeSinceLastClick: Date.now() - this.lastClickTime
        };
    }
    
    /**
     * Validate DOM elements are still available
     * @returns {boolean} True if elements are valid
     */
    validateElements() {
        const buttonValid = this.button && this.button.parentNode;
        const audioValid = !this.audio || this.audio.parentNode;
        
        return buttonValid && audioValid;
    }
}

// Make available globally for browser usage
if (typeof window !== 'undefined') {
    window.TTSButtonController = TTSButtonController;
}