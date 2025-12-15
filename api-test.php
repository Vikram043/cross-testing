<?php
require_once 'config/config.php';
require_once 'includes/api-handler.php';
require_once 'includes/ui-components.php';

$apiHandler = new APIHandler();
$missingConfig = checkConfig();

// Get all APIs for dropdown
$allAPIs = $apiHandler->getAllAPIs();

// Get selected API from URL or default
$selectedAPI = $_GET['api'] ?? 'overview';
$apiInfo = $apiHandler->getAPIInfo($selectedAPI);

// Flatten APIs for dropdown
$apiList = [];
foreach ($allAPIs as $category => $apis) {
    foreach ($apis as $api) {
        $apiList[$api['id']] = $api['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test APIs - eCourts API Tester</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        .test-container {
            min-height: calc(100vh - 200px);
        }
        
        .sidebar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .main-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 30px;
        }
        
        .api-selector {
            position: relative;
        }
        
        .api-selector .form-select {
            padding-right: 40px;
            font-weight: 500;
            border-radius: 10px;
            border: 2px solid rgba(0, 0, 0, 0.1);
        }
        
        .api-selector i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            pointer-events: none;
        }
        
        .tab-content {
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-top: none;
            border-radius: 0 0 10px 10px;
            padding: 20px;
            background: white;
        }
        
        .nav-pills .nav-link {
            border-radius: 10px;
            padding: 10px 20px;
            margin-right: 10px;
            font-weight: 500;
            border: 2px solid transparent;
        }
        
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border-color: var(--secondary-color);
        }
        
        .nav-pills .nav-link:not(.active):hover {
            border-color: rgba(0, 0, 0, 0.1);
        }
        
        .form-actions {
            background: rgba(248, 249, 250, 0.5);
            border-radius: var(--border-radius);
            padding: 20px;
            border-top: 3px solid var(--secondary-color);
        }
        
        .result-container {
            min-height: 300px;
            transition: all 0.3s ease;
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .quick-actions .btn {
            flex: 1;
            min-width: 120px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-bottom: 20px;
                position: static;
            }
            
            .quick-actions .btn {
                min-width: 100px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-gavel me-2"></i>
                eCourts API Tester
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="api-test.php">
                            <i class="fas fa-vial me-1"></i> Test APIs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-history me-1"></i> History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-cog me-1"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container test-container mt-4 mb-5">
        <?php if (!empty($missingConfig)): ?>
        <div class="alert alert-danger glass-card">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Configuration Required</h4>
            <p>Please update <code>config/config.php</code> with your credentials before testing APIs.</p>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="sidebar glass-card">
                    <h5 class="mb-4 text-white">
                        <i class="fas fa-list me-2"></i>API Categories
                    </h5>
                    
                    <div class="list-group list-group-flush">
                        <?php foreach ($allAPIs as $category => $apis): ?>
                        <a href="#category-<?php echo strtolower(str_replace(' ', '-', $category)); ?>" 
                           class="list-group-item list-group-item-action bg-transparent text-white border-0 mb-2 rounded"
                           onclick="filterAPIs('<?php echo strtolower($category); ?>')">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-folder me-2"></i>
                                    <?php echo $category; ?>
                                </span>
                                <span class="badge bg-light text-dark"><?php echo count($apis); ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr class="text-white opacity-25 my-4">
                    
                    <h6 class="text-white mb-3">
                        <i class="fas fa-history me-2"></i>Recent Tests
                    </h6>
                    <div id="testHistory" class="mb-3">
                        <!-- Test history will be loaded via JavaScript -->
                    </div>
                    
                    <div class="text-center">
                        <button class="btn btn-sm btn-outline-light" onclick="clearHistory()">
                            <i class="fas fa-trash me-1"></i> Clear History
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="main-content glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0">
                            <i class="fas fa-vial me-2"></i>API Testing Console
                        </h3>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog me-1"></i> Options
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#configModal">
                                    <i class="fas fa-wrench me-2"></i>Configuration Check
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="clearForm()">
                                    <i class="fas fa-broom me-2"></i>Clear All
                                </a></li>
                                <li><a class="dropdown-item" href="#">
                                    <i class="fas fa-save me-2"></i>Save Test
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- API Selection -->
                    <div class="mb-4">
                        <div class="api-selector">
                            <select class="form-select form-select-lg" id="apiSelect">
                                <option value="">Select an API to test...</option>
                                <?php foreach ($apiList as $id => $name): ?>
                                <option value="<?php echo $id; ?>" <?php echo ($selectedAPI == $id) ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    
                    <!-- API Information -->
                    <div class="alert alert-info mb-4">
                        <h5><i class="fas fa-info-circle me-2"></i><?php echo $apiInfo['name']; ?></h5>
                        <p class="mb-0"><?php echo $apiInfo['description']; ?></p>
                        <?php if ($apiInfo['endpoint']): ?>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <small><strong>Endpoint:</strong> <?php echo BASE_URL . $apiInfo['endpoint']; ?></small>
                            <button class="btn btn-sm btn-outline-info" onclick="copyEndpoint()">
                                <i class="fas fa-copy me-1"></i>Copy
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Test Form -->
                    <form id="apiTestForm">
                        <input type="hidden" name="api_name" value="<?php echo $selectedAPI; ?>">
                        
                        <!-- Parameters Container -->
                        <div id="parametersContainer" class="mb-4">
                            <!-- Parameters will be loaded dynamically -->
                            <?php 
                            // Load initial parameters for selected API
                            if (!empty($apiInfo['parameters'])) {
                                echo '<div class="parameter-group">';
                                echo '<h5><i class="fas fa-sliders-h me-2"></i>API Parameters</h5>';
                                foreach ($apiInfo['parameters'] as $param) {
                                    echo UIComponents::createParameterInput($param);
                                }
                                echo '</div>';
                            } else {
                                echo '<div class="alert alert-info">';
                                echo '<i class="fas fa-info-circle me-2"></i>';
                                echo 'This API doesn\'t require any input parameters.';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions mb-4">
                            <h5><i class="fas fa-play-circle me-2"></i>Test Actions</h5>
                            <div class="quick-actions mt-3">
                                <button type="submit" class="btn btn-lg btn-api" id="testButton">
                                    <i class="fas fa-play me-2"></i>Test API
                                </button>
                                <button type="button" class="btn btn-lg btn-outline-warning" id="loadSample">
                                    <i class="fas fa-vial me-2"></i>Load Sample
                                </button>
                                <button type="button" class="btn btn-lg btn-outline-secondary" id="clearForm">
                                    <i class="fas fa-broom me-2"></i>Clear
                                </button>
                                <button type="button" class="btn btn-lg btn-outline-info" data-bs-toggle="modal" data-bs-target="#helpModal">
                                    <i class="fas fa-question-circle me-2"></i>Help
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Results Container -->
                    <div class="mt-5">
                        <h4 class="mb-4">
                            <i class="fas fa-poll me-2"></i>Test Results
                            <small class="text-muted ms-2" id="resultTimestamp"></small>
                        </h4>
                        
                        <div id="resultContainer">
                            <div class="text-center py-5">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Test Results Yet</h5>
                                <p class="text-muted">Select an API, enter parameters, and click "Test API" to see results here.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <!-- Footer -->
    <footer class="text-white text-center py-4" style="background: rgba(44, 62, 80, 0.9);">
        <div class="container">
            <p class="mb-2">
                <i class="fas fa-gavel me-2"></i>
                eCourts API Tester v1.0 - High Courts API Specifications Version 1.0
            </p>
            <p class="mb-0">
                <small>© 2024 National Informatics Centre, eCommittee, Supreme Court of India</small>
            </p>
        </div>
    </footer>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-question-circle me-2"></i>API Testing Help
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="accordion" id="helpAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#help1">
                                    Getting Started
                                </button>
                            </h2>
                            <div id="help1" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li>Select an API from the dropdown menu</li>
                                        <li>Fill in the required parameters (marked with *)</li>
                                        <li>Click "Test API" to run the test</li>
                                        <li>View results in the Test Results section</li>
                                        <li>Use "Load Sample" to quickly test with sample data</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help2">
                                    Understanding Results
                                </button>
                            </h2>
                            <div id="help2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li><strong>HTTP Status:</strong> The HTTP response code from the server</li>
                                        <li><strong>Response Time:</strong> When the response was received</li>
                                        <li><strong>Token Valid:</strong> Whether the response token verification passed</li>
                                        <li><strong>Data Size:</strong> Size of the response data in bytes</li>
                                        <li><strong>Response Data:</strong> The actual API response (decrypted JSON)</li>
                                        <li><strong>Raw Response:</strong> The original encrypted response from the API</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help3">
                                    Common Error Codes
                                </button>
                            </h2>
                            <div id="help3" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Meaning</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>600</td><td>Invalid CNR number</td></tr>
                                                <tr><td>626</td><td>Invalid token</td></tr>
                                                <tr><td>627</td><td>Invalid department ID</td></tr>
                                                <tr><td>628</td><td>Record not found</td></tr>
                                                <tr><td>629</td><td>Service unresponsive</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Modal -->
    <div class="modal fade" id="configModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-wrench me-2"></i>Configuration Status
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($missingConfig)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        All configuration values are set properly.
                    </div>
                    <p class="mb-0">Your API tester is ready to use!</p>
                    <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Configuration required!
                    </div>
                    <p>The following configuration values need to be set in <code>config/config.php</code>:</p>
                    <ul>
                        <?php foreach ($missingConfig as $config): ?>
                        <li><strong><?php echo $config; ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="mb-0">Without these values, the API tester cannot function properly.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/script.js"></script>
    
    <script>
    // Initialize page
    $(document).ready(function() {
        // Load test history
        loadTestHistory();
        
        // Load parameters for selected API
        $('#apiSelect').trigger('change');
        
        // Form submission handler
        $('#apiTestForm').on('submit', function(e) {
            e.preventDefault();
            testAPI();
        });
        
        // Clear form button
        $('#clearForm').on('click', function() {
            clearForm();
        });
        
        // Load sample data button
        $('#loadSample').on('click', function() {
            loadSampleData();
        });
        
        // API selection change
        $('#apiSelect').on('change', function() {
            loadAPIParameters(this.value);
        });
        
        // Copy endpoint to clipboard
        window.copyEndpoint = function() {
            const endpoint = '<?php echo BASE_URL . $apiInfo["endpoint"]; ?>';
            navigator.clipboard.writeText(endpoint).then(() => {
                showToast('Endpoint copied to clipboard!', 'success');
            });
        };
        
        // Filter APIs by category
        window.filterAPIs = function(category) {
            const $select = $('#apiSelect');
            const $options = $select.find('option');
            
            $select.val(''); // Reset selection
            
            // Show all options first
            $options.show();
            
            if (category) {
                // Hide options that don't belong to the selected category
                // Note: This requires adding data-category attribute to options
                $options.not('[data-category*="' + category + '"]').hide();
            }
        };
        
        // Clear test history
        window.clearHistory = function() {
            if (confirm('Are you sure you want to clear all test history?')) {
                localStorage.removeItem('apiTestHistory');
                loadTestHistory();
                showToast('Test history cleared!', 'info');
            }
        };
        
        // Update result timestamp
        function updateResultTimestamp() {
            $('#resultTimestamp').text('Last updated: ' + new Date().toLocaleString());
        }
        
        // Override displayResult to update timestamp
        const originalDisplayResult = window.displayResult;
        window.displayResult = function(result) {
            originalDisplayResult(result);
            updateResultTimestamp();
        };
    });
    </script>
</body>
</html>