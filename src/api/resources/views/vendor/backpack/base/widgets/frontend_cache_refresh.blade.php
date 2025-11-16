@php
    $widget['wrapper']['class'] = $widget['wrapper']['class'] ?? 'col-md-12';
    $units = config('frontend_cache_refresh.units', []);
    $gridColumns = config('frontend_cache_refresh.widget.grid_columns', 3);
    $showLastRefresh = config('frontend_cache_refresh.widget.show_last_refresh', true);
    $showStatus = config('frontend_cache_refresh.widget.show_status', true);
@endphp

<div class="{{ $widget['wrapper']['class'] }}">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="la la-refresh"></i> {{ config('frontend_cache_refresh.widget.title', 'Frontend Cache Management') }}
            </h3>
            @if($showStatus)
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshCacheStatus()">
                    <i class="la la-sync"></i> Refresh Status
                </button>
            </div>
            @endif
        </div>
        <div class="card-body">
            <p class="text-muted">
                {{ config('frontend_cache_refresh.widget.description', 'Manage and refresh frontend cache from admin panel') }}
            </p>
            
            @if($showStatus)
            <div id="cache-status-alert" class="alert alert-info" style="display: none;">
                <i class="la la-info-circle"></i> <span id="cache-status-message"></span>
            </div>
            @endif
            
            <div class="row" id="cache-refresh-units">
                @foreach($units as $index => $unit)
                @php
                    $unitUrls = is_array($unit['url']) ? $unit['url'] : [$unit['url']];
                    $primaryUrl = $unitUrls[0]; // Use first URL as primary identifier
                    $urlCount = count($unitUrls);
                @endphp
                <div class="col-md-{{ 12 / $gridColumns }} mb-3">
                    <div class="card cache-unit-card" data-unit-url="{{ $primaryUrl }}" data-unit-urls='@json($unitUrls)'>
                        <div class="card-body text-center">
                            <div class="cache-unit-icon mb-2">
                                <i class="la {{ $unit['icon'] ?? 'la-refresh' }} fa-2x text-{{ str_replace('btn-', '', $unit['color'] ?? 'primary') }}"></i>
                            </div>
                            <h5 class="card-title">{{ $unit['title'] }}</h5>
                            <p class="card-text text-muted small">{{ $unit['desc'] }}</p>
                            
                            @if($urlCount > 1)
                            <div class="mb-2">
                                <small class="text-info">
                                    <i class="la la-link"></i> {{ $urlCount }} endpoints
                                </small>
                            </div>
                            @endif
                            
                            @if(isset($unit['timeout']) && $unit['timeout'] === 0)
                            <div class="mb-2">
                                <small class="text-warning">
                                    <i class="la la-clock"></i> Unlimited timeout
                                </small>
                            </div>
                            @endif
                            
                            @if($showLastRefresh)
                            <div class="cache-unit-status mb-2">
                                <small class="text-muted">
                                    <span class="last-refresh-text">Never refreshed</span>
                                </small>
                            </div>
                            @endif
                            
                            @if($showStatus)
                            <div class="cache-unit-indicator mb-2">
                                <span class="badge badge-secondary status-badge">Unknown</span>
                            </div>
                            @endif
                            
                            <button type="button" 
                                    class="btn {{ $unit['color'] ?? 'btn-primary' }} btn-sm cache-refresh-btn"
                                    data-unit-url="{{ $primaryUrl }}"
                                    data-unit-title="{{ $unit['title'] }}"
                                    data-unit-urls='@json($unitUrls)'
                                    onclick="refreshCache(this)">
                                <i class="la la-refresh refresh-icon"></i>
                                {{ $unit['button'] }}
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('after_scripts')
<script>
$(document).ready(function() {
    // Load initial status
    refreshCacheStatus();
    
    // Auto-refresh status every 30 seconds
    setInterval(refreshCacheStatus, 30000);
});

function refreshCache(buttonElement) {
    const $button = $(buttonElement);
    const unitUrl = $button.data('unit-url');
    const unitTitle = $button.data('unit-title');
    const unitUrls = $button.data('unit-urls') || [unitUrl];
    
    const card = $(`.cache-unit-card[data-unit-url="${unitUrl}"]`);
    const refreshIcon = $button.find('.refresh-icon');
    
    // Disable button and show loading state
    $button.prop('disabled', true);
    refreshIcon.addClass('fa-spin');
    
    // Update status indicator
    const statusBadge = card.find('.status-badge');
    statusBadge.removeClass('badge-success badge-danger badge-warning badge-secondary')
              .addClass('badge-info')
              .text('Running...');
    
    // Show loading alert with URLs info
    const urlsText = unitUrls.length > 1 ? ` (${unitUrls.length} endpoints)` : '';
    showAlert('info', `Starting cache refresh for ${unitTitle}${urlsText}...`);
    
    $.ajax({
        url: '{{ backpack_url("frontend-cache-refresh") }}',
        method: 'POST',
        data: {
            unit_url: unitUrl,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                const urlInfo = response.data && response.data.total_urls > 1 
                    ? ` (${response.data.total_urls} URLs processed)` 
                    : '';
                showAlert('success', response.message + urlInfo);
                
                // Update status badge
                statusBadge.removeClass('badge-info badge-danger badge-warning badge-secondary')
                          .addClass('badge-success')
                          .text('Success');
                
                // Update last refresh time
                updateLastRefreshTime(card, new Date());
                
                // Refresh status after a delay to get updated info
                setTimeout(function() {
                    refreshCacheStatus();
                }, 2000);
                
            } else {
                showAlert('danger', response.message || 'Cache refresh failed');
                statusBadge.removeClass('badge-info badge-success badge-warning badge-secondary')
                          .addClass('badge-danger')
                          .text('Failed');
            }
        },
        error: function(xhr) {
            let errorMessage = 'An error occurred while refreshing cache';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            showAlert('danger', errorMessage);
            statusBadge.removeClass('badge-info badge-success badge-warning badge-secondary')
                      .addClass('badge-danger')
                      .text('Error');
        },
        complete: function() {
            // Re-enable button and stop loading animation
            setTimeout(function() {
                $button.prop('disabled', false);
                refreshIcon.removeClass('fa-spin');
            }, 1000);
        }
    });
}

function refreshCacheStatus() {
    $.ajax({
        url: '{{ backpack_url("frontend-cache-refresh/status") }}',
        method: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                response.data.forEach(function(item) {
                    // For units with multiple URLs, use the first URL as identifier
                    const unitUrls = item.urls || [item.unit.url];
                    const primaryUrl = Array.isArray(item.unit.url) ? item.unit.url[0] : item.unit.url;
                    const card = $(`.cache-unit-card[data-unit-url="${primaryUrl}"]`);
                    const statusBadge = card.find('.status-badge');
                    const lastRefreshText = card.find('.last-refresh-text');
                    
                    // Update status badge
                    statusBadge.removeClass('badge-success badge-danger badge-warning badge-secondary badge-info');
                    
                    let statusText = 'Unknown';
                    switch(item.status) {
                        case 'success':
                            statusBadge.addClass('badge-success');
                            statusText = item.total_urls > 1 ? `Success (${item.total_urls})` : 'Success';
                            break;
                        case 'failed':
                            statusBadge.addClass('badge-danger');
                            statusText = item.total_urls > 1 ? `Failed (${item.total_urls})` : 'Failed';
                            break;
                        case 'running':
                            statusBadge.addClass('badge-info');
                            statusText = item.total_urls > 1 ? `Running... (${item.total_urls})` : 'Running...';
                            break;
                        case 'never_run':
                            statusBadge.addClass('badge-secondary');
                            statusText = 'Never Run';
                            break;
                        default:
                            statusBadge.addClass('badge-secondary');
                            statusText = 'Unknown';
                    }
                    
                    statusBadge.text(statusText);
                    
                    // Update last refresh time
                    if (item.last_run) {
                        const lastRunDate = new Date(item.last_run * 1000);
                        updateLastRefreshTime(card, lastRunDate);
                    }
                });
            }
        },
        error: function() {
            console.warn('Failed to refresh cache status');
        }
    });
}

function updateLastRefreshTime(card, date) {
    const lastRefreshText = card.find('.last-refresh-text');
    const timeAgo = getTimeAgo(date);
    lastRefreshText.text(`Last: ${timeAgo}`);
}

function getTimeAgo(date) {
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return 'Just now';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} day${days > 1 ? 's' : ''} ago`;
    }
}

function showAlert(type, message) {
    const alertElement = $('#cache-status-alert');
    const messageElement = $('#cache-status-message');
    
    // Remove existing alert classes
    alertElement.removeClass('alert-success alert-danger alert-warning alert-info alert-secondary')
                .addClass(`alert-${type}`);
    
    messageElement.text(message);
    alertElement.show();
    
    // Auto-hide after 5 seconds for success messages
    if (type === 'success' || type === 'info') {
        setTimeout(function() {
            alertElement.fadeOut();
        }, 5000);
    }
}
</script>

<style>
.cache-unit-card {
    transition: all 0.3s ease;
    border: 1px solid #e3e6f0;
}

.cache-unit-card:hover {
    border-color: #5a5c69;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.cache-unit-icon {
    opacity: 0.8;
}

.cache-refresh-btn {
    min-width: 120px;
}

.cache-refresh-btn:disabled {
    opacity: 0.6;
}

.status-badge {
    font-size: 0.75rem;
    min-width: 70px;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.fa-spin {
    animation: spin 1s linear infinite;
}
</style>
@endpush