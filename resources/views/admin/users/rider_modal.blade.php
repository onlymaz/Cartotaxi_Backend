<div style="padding: 1.5rem;">
    <form class="ajax-form-update" method="POST" action="{{route('users.rider_order_save',$order->id)}}" enctype="multipart/form-data" id="riderAssignmentForm">
        @csrf
        <input type="hidden" name="_method" value="PATCH">
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--ct-gray-700);">{{__('messages.select_a_rider', [], 'en') ?: 'Select a Rider'}}</label>
            
            <!-- Autocomplete Search Input -->
            <div style="position: relative;">
                <input type="text" id="riderSearchInput" placeholder="Type rider name to search..." style="width: 100%; padding: 0.625rem 1rem; padding-right: 2.5rem; border: 1px solid var(--ct-gray-300); border-radius: 0.5rem; font-size: 0.875rem; background: white;" autocomplete="off">
                <i class="fas fa-search" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--ct-gray-400); pointer-events: none;"></i>
                
                <!-- Suggestions Dropdown -->
                <div id="riderSuggestions" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid var(--ct-gray-300); border-radius: 0.5rem; margin-top: 0.25rem; max-height: 300px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Suggestions will be inserted here -->
                </div>
    </div>
            
            <!-- Hidden select for form submission -->
            <select name="rider_id" id="riderSelect" style="display: none;">
                <option value="">{{__('messages.select_a_rider', [], 'en') ?: 'Select a Rider'}}</option>
                        @foreach($riders as $rider)
                            @if($rider->order)
                        <option value="{{$rider->id}}" data-rider-name="{{strtolower($rider->full_name)}}" data-rider-display="{{$rider->full_name}} → {{__('messages.on_ride', [], 'en') ?: 'On Ride'}} → {{$rider->order['end_location']}}" {{$order->rider_id == $rider->id ? 'selected' : ''}}>
                            {{$rider->full_name}} → {{__('messages.on_ride', [], 'en') ?: 'On Ride'}} → {{$rider->order['end_location']}}
                                </option>
                            @else
                        <option value="{{$rider->id}}" data-rider-name="{{strtolower($rider->full_name)}}" data-rider-display="{{$rider->full_name}} → {{__('messages.free', [], 'en') ?: 'Free'}}" {{$order->rider_id == $rider->id ? 'selected' : ''}}>
                            {{$rider->full_name}} → {{__('messages.free', [], 'en') ?: 'Free'}}
                                </option>
                            @endif
                        @endforeach
                    </select>
            
            <!-- Selected Rider Display -->
            <div id="selectedRiderDisplay" style="margin-top: 0.75rem; padding: 0.75rem; background: var(--ct-gray-50); border-radius: 0.5rem; display: none;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <span style="font-size: 0.875rem; color: var(--ct-gray-600);">Selected:</span>
                        <span id="selectedRiderName" style="font-weight: 600; color: var(--ct-gray-900); margin-left: 0.5rem;"></span>
                </div>
                    <button type="button" id="clearSelection" style="background: none; border: none; color: var(--ct-gray-500); cursor: pointer; padding: 0.25rem 0.5rem; font-size: 0.875rem;" title="Clear selection">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            @if($order->rider_id)
                <small style="margin-top: 0.5rem; display: block; color: var(--ct-gray-500); font-size: 0.875rem;">
                    Current rider will be replaced with the selected rider.
                </small>
            @endif
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
            <button type="button" onclick="if(typeof closeModal === 'function') closeModal();" class="nova-btn nova-btn-secondary" style="padding: 0.625rem 1.5rem;">Cancel</button>
            <button type="submit" class="nova-btn nova-btn-primary" style="padding: 0.625rem 1.5rem; background: var(--ct-accent); color: var(--ct-primary);">Save</button>
            </div>
        </form>
    </div>

<script>
// Initialize rider search after modal content is loaded
(function() {
    function initRiderSearch() {
        console.log('Initializing rider search...');
        var searchInput = document.getElementById('riderSearchInput');
        var riderSelect = document.getElementById('riderSelect');
        var suggestionsDiv = document.getElementById('riderSuggestions');
        var selectedRiderDisplay = document.getElementById('selectedRiderDisplay');
        var selectedRiderName = document.getElementById('selectedRiderName');
        var clearSelectionBtn = document.getElementById('clearSelection');
        var allRiders = [];
        var selectedRiderId = null;
        
        // Check if elements exist
        if (!searchInput || !riderSelect || !suggestionsDiv) {
            console.error('Rider search elements not found');
            return;
        }
        
        console.log('Initializing rider search, found', riderSelect.querySelectorAll('option').length, 'riders');
        
        // Get all riders from select options
        var options = riderSelect.querySelectorAll('option');
        options.forEach(function(option) {
            if (option.value) {
                var riderName = option.getAttribute('data-rider-name') || option.textContent.toLowerCase();
                var riderDisplay = option.getAttribute('data-rider-display') || option.textContent;
                allRiders.push({
                    id: option.value,
                    name: riderName,
                    display: riderDisplay
                });
            }
        });
        
        console.log('Loaded', allRiders.length, 'riders for search');
        
        // Set initial selected rider if exists
        var selectedOption = riderSelect.querySelector('option[selected]');
        if (selectedOption && selectedOption.value) {
            selectedRiderId = selectedOption.value;
            var displayText = selectedOption.getAttribute('data-rider-display') || selectedOption.textContent;
            if (searchInput) searchInput.value = displayText;
            if (selectedRiderName) selectedRiderName.textContent = displayText;
            if (selectedRiderDisplay) selectedRiderDisplay.style.display = 'block';
        }
        
        // Show suggestions as user types
        searchInput.addEventListener('input', function(e) {
            var searchTerm = e.target.value.toLowerCase().trim();
            console.log('Search term:', searchTerm);
            
            if (searchTerm.length === 0) {
                suggestionsDiv.style.display = 'none';
                return;
            }
            
            // Filter riders based on search term
            var matches = allRiders.filter(function(rider) {
                var nameMatch = rider.name.includes(searchTerm);
                var displayMatch = rider.display.toLowerCase().includes(searchTerm);
                return nameMatch || displayMatch;
            });
            
            console.log('Found', matches.length, 'matches');
            
            if (matches.length === 0) {
                suggestionsDiv.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--ct-gray-500);">No riders found</div>';
                suggestionsDiv.style.display = 'block';
                return;
            }
            
            // Build suggestions HTML
            var suggestionsHTML = '';
            matches.forEach(function(rider) {
                var parts = rider.display.split(' → ');
                var name = parts[0] || rider.display;
                var status = parts.slice(1).join(' → ') || '';
                
                suggestionsHTML += '<div class="rider-suggestion" data-rider-id="' + rider.id + '" data-rider-display="' + escapeHtml(rider.display) + '" style="padding: 0.75rem 1rem; cursor: pointer; border-bottom: 1px solid var(--ct-gray-100); transition: background 0.2s;">' +
                    '<div style="font-weight: 500; color: var(--ct-gray-900);">' + escapeHtml(name) + '</div>' +
                    (status ? '<div style="font-size: 0.75rem; color: var(--ct-gray-500); margin-top: 0.25rem;">' + escapeHtml(status) + '</div>' : '') +
                    '</div>';
            });
            
            suggestionsDiv.innerHTML = suggestionsHTML;
            suggestionsDiv.style.display = 'block';
            
            // Add click handlers to suggestions
            var suggestionItems = suggestionsDiv.querySelectorAll('.rider-suggestion');
            suggestionItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    var riderId = this.getAttribute('data-rider-id');
                    var riderDisplay = this.getAttribute('data-rider-display');
                    
                    console.log('Selected rider:', riderId, riderDisplay);
                    
                    // Update hidden select
                    riderSelect.value = riderId;
                    selectedRiderId = riderId;
                    
                    // Update display
                    searchInput.value = riderDisplay;
                    if (selectedRiderName) selectedRiderName.textContent = riderDisplay;
                    if (selectedRiderDisplay) selectedRiderDisplay.style.display = 'block';
                    
                    // Hide suggestions
                    suggestionsDiv.style.display = 'none';
                });
                
                // Add hover effects
                item.addEventListener('mouseenter', function() {
                    this.style.background = 'var(--ct-gray-50)';
                });
                item.addEventListener('mouseleave', function() {
                    this.style.background = 'white';
                });
            });
        });
        
        // Show all riders when input is focused and empty
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length === 0 && allRiders.length > 0) {
                var suggestionsHTML = '';
                allRiders.forEach(function(rider) {
                    var parts = rider.display.split(' → ');
                    var name = parts[0] || rider.display;
                    var status = parts.slice(1).join(' → ') || '';
                    
                    suggestionsHTML += '<div class="rider-suggestion" data-rider-id="' + rider.id + '" data-rider-display="' + escapeHtml(rider.display) + '" style="padding: 0.75rem 1rem; cursor: pointer; border-bottom: 1px solid var(--ct-gray-100); transition: background 0.2s;">' +
                        '<div style="font-weight: 500; color: var(--ct-gray-900);">' + escapeHtml(name) + '</div>' +
                        (status ? '<div style="font-size: 0.75rem; color: var(--ct-gray-500); margin-top: 0.25rem;">' + escapeHtml(status) + '</div>' : '') +
                        '</div>';
                });
                
                suggestionsDiv.innerHTML = suggestionsHTML;
                suggestionsDiv.style.display = 'block';
                
                // Add click handlers
                var suggestionItems = suggestionsDiv.querySelectorAll('.rider-suggestion');
                suggestionItems.forEach(function(item) {
                    item.addEventListener('click', function() {
                        var riderId = this.getAttribute('data-rider-id');
                        var riderDisplay = this.getAttribute('data-rider-display');
                        
                        riderSelect.value = riderId;
                        selectedRiderId = riderId;
                        searchInput.value = riderDisplay;
                        if (selectedRiderName) selectedRiderName.textContent = riderDisplay;
                        if (selectedRiderDisplay) selectedRiderDisplay.style.display = 'block';
                        suggestionsDiv.style.display = 'none';
                    });
                    
                    item.addEventListener('mouseenter', function() {
                        this.style.background = 'var(--ct-gray-50)';
                    });
                    item.addEventListener('mouseleave', function() {
                        this.style.background = 'white';
                    });
                });
            }
        });
        
        // Hide suggestions when clicking outside
        setTimeout(function() {
            document.addEventListener('click', function(e) {
                if (searchInput && suggestionsDiv && 
                    !searchInput.contains(e.target) && 
                    !suggestionsDiv.contains(e.target)) {
                    suggestionsDiv.style.display = 'none';
                }
            });
        }, 100);
        
        // Handle keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            var visibleSuggestions = suggestionsDiv.querySelectorAll('.rider-suggestion');
            var currentIndex = -1;
            
            visibleSuggestions.forEach(function(item, index) {
                if (item.style.background === 'var(--ct-gray-50)' || item.classList.contains('highlighted')) {
                    currentIndex = index;
                }
            });
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (currentIndex < visibleSuggestions.length - 1) {
                    visibleSuggestions.forEach(function(item) {
                        item.style.background = 'white';
                        item.classList.remove('highlighted');
                    });
                    visibleSuggestions[currentIndex + 1].style.background = 'var(--ct-gray-50)';
                    visibleSuggestions[currentIndex + 1].classList.add('highlighted');
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (currentIndex > 0) {
                    visibleSuggestions.forEach(function(item) {
                        item.style.background = 'white';
                        item.classList.remove('highlighted');
                    });
                    visibleSuggestions[currentIndex - 1].style.background = 'var(--ct-gray-50)';
                    visibleSuggestions[currentIndex - 1].classList.add('highlighted');
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                var highlighted = suggestionsDiv.querySelector('.highlighted');
                if (highlighted) {
                    highlighted.click();
                } else if (visibleSuggestions.length === 1) {
                    visibleSuggestions[0].click();
                }
            } else if (e.key === 'Escape') {
                suggestionsDiv.style.display = 'none';
            }
        });
        
        // Clear selection
        if (clearSelectionBtn) {
            clearSelectionBtn.addEventListener('click', function() {
                riderSelect.value = '';
                selectedRiderId = null;
                searchInput.value = '';
                if (selectedRiderDisplay) selectedRiderDisplay.style.display = 'none';
                suggestionsDiv.style.display = 'none';
            });
        }
        
        // Prevent form submission on Cancel button
        var form = document.getElementById('riderAssignmentForm');
        if (form) {
            var cancelBtn = form.querySelector('button[type="button"]');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (typeof closeModal === 'function') {
                        closeModal();
                    }
                });
            }
        }
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    // Initialize when script runs (content should be loaded)
    if (typeof jQuery !== 'undefined') {
        // Use jQuery ready and also listen for modal content loaded
        jQuery(function() {
            setTimeout(initRiderSearch, 100);
        });
        
        jQuery(document).on('modalContentLoaded', function() {
            setTimeout(initRiderSearch, 50);
        });
    }
    
    // Try immediate initialization
    setTimeout(function() {
        initRiderSearch();
    }, 50);
    
    // Multiple fallback attempts
    setTimeout(initRiderSearch, 200);
    setTimeout(initRiderSearch, 500);
    setTimeout(initRiderSearch, 1000);
})();
</script>
