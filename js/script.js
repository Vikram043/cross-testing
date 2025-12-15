/**
 * eCourts API Tester - Main JavaScript File
 */

$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-bs-toggle="popover"]').popover();
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 1000);
        }
    });
    
    // API Test Form Handler
    $('#apiTestForm').on('submit', function(e) {
        e.preventDefault();
        testAPI();
    });
    
    // Parameter input validation
    $('.parameter-input').on('blur', function() {
        validateParameter(this);
    });
    
    // Real-time validation for required fields
    $('.parameter-input[required]').on('input', function() {
        validateParameter(this);
    });
    
    // Toggle between JSON and Table view
    $('#toggleView').on('click', function() {
        toggleView();
    });
    
    // Copy to clipboard functionality
    $('.copy-btn').on('click', function() {
        const target = $(this).data('target');
        copyToClipboard(target);
    });
    
    // Download JSON functionality
    $('.download-json').on('click', function() {
        downloadJSON();
    });
    
    // Clear form button
    $('#clearForm').on('click', function() {
        clearForm();
    });
    
    // Load sample data for testing
    $('#loadSample').on('click', function() {
        loadSampleData();
    });
    
    // API selection change
    $('#apiSelect').on('change', function() {
        loadAPIParameters(this.value);
    });
    
    // Initialize the first API if parameter exists
    const urlParams = new URLSearchParams(window.location.search);
    const apiParam = urlParams.get('api');
    if (apiParam) {
        $('#apiSelect').val(apiParam).trigger('change');
    }
});

/**
 * Test the selected API
 */
function testAPI() {
    // Show loading spinner
    showLoading();
    
    // Get form data
    const formData = $('#apiTestForm').serializeArray();
    const apiName = $('#apiSelect').val();
    
    // Build parameters object
    const params = {};
    formData.forEach(item => {
        if (item.value.trim() !== '') {
            params[item.name] = item.value.trim();
        }
    });
    
    // Make AJAX request
    $.ajax({
        url: 'process-test.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'test_api',
            api: apiName,
            params: JSON.stringify(params)
        },
        success: function(response) {
            displayResult(response);
        },
        error: function(xhr, status, error) {
            displayError(xhr.responseJSON || { error: 'Request failed: ' + error });
        },
        complete: function() {
            hideLoading();
        }
    });
}

/**
 * Display API test result
 */
function displayResult(result) {
    const resultContainer = $('#resultContainer');
    
    if (result.success) {
        // Create result HTML
        let html = `
            <div class="result-panel fade-in">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4><i class="fas fa-poll me-2"></i>API Test Result</h4>
                    <span class="badge bg-success">SUCCESS</span>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fas fa-code me-2"></i>HTTP Status</h6>
                                <h3 class="text-success">${result.http_code}</h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fas fa-clock me-2"></i>Response Time</h6>
                                <h3 class="text-info">${result.response_time || 'N/A'}</h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fas fa-shield-alt me-2"></i>Token Valid</h6>
                                <h3 class="${result.token_valid ? 'text-success' : 'text-danger'}">
                                    ${result.token_valid ? 'YES' : 'NO'}
                                </h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fas fa-database me-2"></i>Data Size</h6>
                                <h3 class="text-primary">
                                    ${result.data ? JSON.stringify(result.data).length + ' bytes' : 'N/A'}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
        `;
        
        // Add data display if available
        if (result.data) {
            html += `
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5><i class="fas fa-table me-2"></i>Response Data</h5>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('#responseData')">
                                <i class="fas fa-copy me-1"></i>Copy
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="downloadJSON()">
                                <i class="fas fa-download me-1"></i>Download
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="toggleView()">
                                <i class="fas fa-exchange-alt me-1"></i>Toggle View
                            </button>
                        </div>
                    </div>
                    
                    <div id="jsonViewer" class="border rounded">
                        <pre id="responseData" class="mb-0 p-3" style="max-height: 500px; overflow-y: auto; background: #f8f9fa;">
${JSON.stringify(result.data, null, 2)}
                        </pre>
                    </div>
                    
                    <div id="tableView" class="border rounded mt-3 d-none" style="max-height: 500px; overflow-y: auto;">
                        ${createDataTable(result.data)}
                    </div>
                </div>
            `;
        }
        
        // Add raw response if available
        if (result.raw_response) {
            html += `
                <div class="mb-4">
                    <h5><i class="fas fa-file-code me-2"></i>Raw Response</h5>
                    <div class="border rounded">
                        <pre class="mb-0 p-3" style="max-height: 300px; overflow-y: auto; background: #f8f9fa;">
${JSON.stringify(result.raw_response, null, 2)}
                        </pre>
                    </div>
                </div>
            `;
        }
        
        html += '</div>';
        
        resultContainer.html(html);
        
        // Syntax highlighting for JSON
        highlightJSON();
        
        // Save to history
        saveToHistory(result, apiName);
        
    } else {
        // Display error
        displayError(result);
    }
    
    // Scroll to results
    $('html, body').animate({
        scrollTop: resultContainer.offset().top - 100
    }, 500);
}

/**
 * Display error message
 */
function displayError(error) {
    const resultContainer = $('#resultContainer');
    
    let html = `
        <div class="alert alert-danger fade-in">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>API Test Failed</h5>
            <p class="mb-2"><strong>Error:</strong> ${error.error || 'Unknown error'}</p>
            <p class="mb-0"><strong>HTTP Status:</strong> ${error.http_code || 'N/A'}</p>
    `;
    
    if (error.raw_response) {
        html += `
            <button class="btn btn-sm btn-outline-danger mt-3" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#rawError">
                <i class="fas fa-code me-1"></i>View Raw Response
            </button>
            <div class="collapse mt-2" id="rawError">
                <pre class="bg-dark text-light p-3 rounded"><code>
${JSON.stringify(error.raw_response, null, 2)}
                </code></pre>
            </div>
        `;
    }
    
    html += '</div>';
    
    resultContainer.html(html);
}

/**
 * Load API parameters for selected API
 */
function loadAPIParameters(apiName) {
    const paramsContainer = $('#parametersContainer');
    
    if (!apiName) {
        paramsContainer.html('<div class="alert alert-info">Select an API to view parameters</div>');
        return;
    }
    
    // Show loading
    paramsContainer.html(`
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading parameters...</p>
        </div>
    `);
    
    // Get API parameters via AJAX
    $.ajax({
        url: 'process-test.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'get_api_parameters',
            api: apiName
        },
        success: function(response) {
            if (response.success) {
                displayAPIParameters(response.parameters, response.info);
            } else {
                paramsContainer.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Failed to load parameters: ${response.error}
                    </div>
                `);
            }
        },
        error: function() {
            paramsContainer.html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Failed to load parameters. Please try again.
                </div>
            `);
        }
    });
}

/**
 * Display API parameters in form
 */
function displayAPIParameters(parameters, apiInfo) {
    const paramsContainer = $('#parametersContainer');
    
    if (!parameters || parameters.length === 0) {
        paramsContainer.html(`
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                This API doesn't require any input parameters.
            </div>
        `);
        return;
    }
    
    let html = `
        <div class="parameter-group">
            <h5><i class="fas fa-sliders-h me-2"></i>API Parameters</h5>
            <p class="text-muted mb-3">${apiInfo.description}</p>
    `;
    
    parameters.forEach(param => {
        html += `
            <div class="mb-3">
                <label for="${param.key}" class="form-label">
                    ${param.label}
                    ${param.required ? '<span class="text-danger">*</span>' : ''}
                </label>
        `;
        
        switch (param.type) {
            case 'text':
                html += `
                    <input type="text" 
                           class="form-control parameter-input" 
                           id="${param.key}" 
                           name="${param.key}" 
                           placeholder="${param.placeholder || ''}"
                           ${param.required ? 'required' : ''}
                           ${param.pattern ? 'pattern="' + param.pattern + '"' : ''}>
                `;
                break;
                
            case 'number':
                html += `
                    <input type="number" 
                           class="form-control parameter-input" 
                           id="${param.key}" 
                           name="${param.key}"
                           ${param.required ? 'required' : ''}>
                `;
                break;
                
            case 'date':
                html += `
                    <input type="date" 
                           class="form-control parameter-input" 
                           id="${param.key}" 
                           name="${param.key}"
                           ${param.required ? 'required' : ''}>
                `;
                break;
                
            case 'select':
                html += `
                    <select class="form-select parameter-input" 
                            id="${param.key}" 
                            name="${param.key}"
                            ${param.required ? 'required' : ''}>
                        <option value="">Select...</option>
                `;
                if (param.options) {
                    param.options.forEach(option => {
                        html += `<option value="${option.value}">${option.label}</option>`;
                    });
                }
                html += `</select>`;
                break;
                
            case 'textarea':
                html += `
                    <textarea class="form-control parameter-input" 
                              id="${param.key}" 
                              name="${param.key}" 
                              rows="3"
                              ${param.required ? 'required' : ''}></textarea>
                `;
                break;
        }
        
        if (param.description) {
            html += `<div class="form-text">${param.description}</div>`;
        }
        
        html += `</div>`;
    });
    
    html += `</div>`;
    
    paramsContainer.html(html);
    
    // Initialize validation for new inputs
    $('.parameter-input[required]').on('input', function() {
        validateParameter(this);
    });
}

/**
 * Validate a parameter input
 */
function validateParameter(input) {
    const $input = $(input);
    const value = $input.val().trim();
    const isRequired = $input.prop('required');
    const pattern = $input.attr('pattern');
    
    // Clear previous validation
    $input.removeClass('is-valid is-invalid');
    $input.next('.invalid-feedback').remove();
    
    // Check required field
    if (isRequired && !value) {
        $input.addClass('is-invalid');
        $input.after('<div class="invalid-feedback">This field is required</div>');
        return false;
    }
    
    // Check pattern if exists
    if (pattern && value) {
        const regex = new RegExp(pattern);
        if (!regex.test(value)) {
            $input.addClass('is-invalid');
            $input.after('<div class="invalid-feedback">Invalid format</div>');
            return false;
        }
    }
    
    // If we got here and there's a value, it's valid
    if (value) {
        $input.addClass('is-valid');
    }
    
    return true;
}

/**
 * Show loading spinner
 */
function showLoading() {
    const resultContainer = $('#resultContainer');
    resultContainer.html(`
        <div class="text-center py-5 fade-in">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h4 class="mt-3 text-primary">Testing API...</h4>
            <p class="text-muted">Please wait while we process your request</p>
            <div class="progress mt-4" style="height: 8px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
            </div>
        </div>
    `);
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    // Loading is replaced by results, so nothing to do here
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(selector) {
    const element = $(selector);
    const text = element.text();
    
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        showToast('Failed to copy to clipboard', 'danger');
    });
}

/**
 * Download JSON data
 */
function downloadJSON() {
    const data = $('#responseData').text();
    const blob = new Blob([data], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    
    a.href = url;
    a.download = `eCourts-API-Result-${Date.now()}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
    showToast('File downloaded!', 'success');
}

/**
 * Toggle between JSON and Table view
 */
function toggleView() {
    const jsonViewer = $('#jsonViewer');
    const tableView = $('#tableView');
    
    if (jsonViewer.is(':visible')) {
        jsonViewer.addClass('d-none');
        tableView.removeClass('d-none');
    } else {
        tableView.addClass('d-none');
        jsonViewer.removeClass('d-none');
    }
}

/**
 * Create HTML table from data
 */
function createDataTable(data, level = 0) {
    if (!data || typeof data !== 'object') {
        return '<div class="p-3 text-muted">No data available</div>';
    }
    
    // Handle arrays
    if (Array.isArray(data)) {
        let html = '<div class="p-3">';
        data.forEach((item, index) => {
            html += `
                <div class="card mb-2">
                    <div class="card-header bg-light">
                        Item ${index + 1}
                    </div>
                    <div class="card-body">
                        ${createDataTable(item, level + 1)}
                    </div>
                </div>
            `;
        });
        html += '</div>';
        return html;
    }
    
    // Handle objects
    let html = `
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    Object.entries(data).forEach(([key, value]) => {
        const cellId = `cell-${level}-${key.replace(/\s+/g, '-')}`;
        
        html += '<tr>';
        html += `<td><strong>${escapeHtml(key)}</strong></td>`;
        html += '<td>';
        
        if (value && typeof value === 'object') {
            const itemCount = Array.isArray(value) ? value.length : Object.keys(value).length;
            html += `
                <button class="btn btn-sm btn-outline-primary" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#${cellId}">
                    Show ${itemCount} item${itemCount !== 1 ? 's' : ''}
                </button>
                <div class="collapse mt-2" id="${cellId}">
                    ${createDataTable(value, level + 1)}
                </div>
            `;
        } else if (typeof value === 'boolean') {
            html += `<span class="badge bg-${value ? 'success' : 'danger'}">${value ? 'true' : 'false'}</span>`;
        } else if (value === null || value === undefined) {
            html += '<span class="text-muted">null</span>';
        } else if (value === '') {
            html += '<span class="text-muted">(empty)</span>';
        } else {
            html += escapeHtml(String(value));
        }
        
        html += '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    return html;
}

/**
 * Clear form inputs
 */
function clearForm() {
    $('#apiTestForm')[0].reset();
    $('.parameter-input').removeClass('is-valid is-invalid');
    $('.invalid-feedback').remove();
    $('#resultContainer').empty();
    showToast('Form cleared!', 'info');
}

/**
 * Load sample data for testing
 */
function loadSampleData() {
    const apiName = $('#apiSelect').val();
    
    // Sample data for different APIs
    const sampleData = {
        'search_by_cnr': {
            'cino': 'JKHC020000132019'
        },
        'show_business': {
            'cino': 'JKHC020000132019',
            'business_date': '2019-07-29'
        },
        'search_by_party_name': {
            'est_code': 'JKHC02',
            'pend_disp': 'P',
            'litigant_name': 'RAM',
            'reg_year': '2019'
        },
        'state_master': {} // No parameters needed
    };
    
    const data = sampleData[apiName];
    if (data) {
        Object.entries(data).forEach(([key, value]) => {
            $(`[name="${key}"]`).val(value).trigger('input');
        });
        showToast('Sample data loaded!', 'success');
    } else {
        showToast('No sample data available for this API', 'warning');
    }
}

/**
 * Save test to history
 */
function saveToHistory(result, apiName) {
    let history = JSON.parse(localStorage.getItem('apiTestHistory') || '[]');
    
    const test = {
        id: Date.now(),
        api: apiName,
        timestamp: new Date().toISOString(),
        success: result.success,
        status_code: result.http_code,
        data_size: result.data ? JSON.stringify(result.data).length : 0
    };
    
    history.unshift(test);
    history = history.slice(0, 50); // Keep only last 50 tests
    
    localStorage.setItem('apiTestHistory', JSON.stringify(history));
}

/**
 * Load test history
 */
function loadTestHistory() {
    const history = JSON.parse(localStorage.getItem('apiTestHistory') || '[]');
    const historyContainer = $('#testHistory');
    
    if (history.length === 0) {
        historyContainer.html(`
            <div class="text-center py-5">
                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No test history yet</h5>
                <p class="text-muted">Test some APIs to see your history here</p>
            </div>
        `);
        return;
    }
    
    let html = '<div class="list-group">';
    
    history.forEach(test => {
        const statusClass = test.success ? 'success' : 'danger';
        const date = new Date(test.timestamp).toLocaleString();
        
        html += `
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${test.api}</h6>
                    <small class="text-muted">${date}</small>
                </div>
                <p class="mb-1">
                    <span class="badge bg-${statusClass} me-2">${test.success ? 'SUCCESS' : 'FAILED'}</span>
                    <span class="badge bg-info">${test.status_code}</span>
                    ${test.data_size ? `<span class="badge bg-secondary ms-2">${test.data_size} bytes</span>` : ''}
                </p>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="viewHistoryTest(${test.id})">
                    <i class="fas fa-eye me-1"></i>View Details
                </button>
            </div>
        `;
    });
    
    html += '</div>';
    historyContainer.html(html);
}

/**
 * View historical test result
 */
function viewHistoryTest(testId) {
    const history = JSON.parse(localStorage.getItem('apiTestHistory') || '[]');
    const test = history.find(t => t.id === testId);
    
    if (test) {
        // In a real app, you might store the full result
        // For now, just show basic info
        alert(`Test Details:\n\nAPI: ${test.api}\nStatus: ${test.success ? 'Success' : 'Failed'}\nHTTP Code: ${test.status_code}\nTimestamp: ${new Date(test.timestamp).toLocaleString()}`);
    }
}

/**
 * Syntax highlighting for JSON
 */
function highlightJSON() {
    const jsonElement = $('#responseData');
    if (!jsonElement.length) return;
    
    const jsonText = jsonElement.text();
    let highlighted = jsonText;
    
    // Highlight JSON keys
    highlighted = highlighted.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?)/g, function(match) {
        let cls = 'json-key';
        if (/:$/.test(match)) {
            cls = 'json-key';
        } else if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                cls = 'json-key';
            } else {
                cls = 'json-string';
            }
        }
        return '<span class="' + cls + '">' + match + '</span>';
    });
    
    // Highlight numbers
    highlighted = highlighted.replace(/\b(-?\d+\.?\d*([eE][+-]?\d+)?)\b/g, '<span class="json-number">$1</span>');
    
    // Highlight booleans and null
    highlighted = highlighted.replace(/\b(true|false|null)\b/g, '<span class="json-boolean">$1</span>');
    
    jsonElement.html(highlighted);
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    // Remove existing toasts
    $('.toast').remove();
    
    const toastId = 'toast-' + Date.now();
    const toast = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${getToastIcon(type)} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    $('#toastContainer').append(toast);
    
    const bsToast = new bootstrap.Toast(document.getElementById(toastId), {
        delay: 3000
    });
    bsToast.show();
}

/**
 * Get icon for toast type
 */
function getToastIcon(type) {
    const icons = {
        'success': 'check-circle',
        'danger': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Escape HTML special characters
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Format bytes to human readable format
 */
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

/**
 * Debounce function for performance
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function for performance
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Initialize when page loads
window.addEventListener('load', function() {
    // Load test history if container exists
    if ($('#testHistory').length) {
        loadTestHistory();
    }
    
    // Initialize API selection if exists
    if ($('#apiSelect').length && $('#apiSelect').val()) {
        loadAPIParameters($('#apiSelect').val());
    }
});