<?php
require_once 'config/config.php';
require_once 'includes/api-handler.php';

$apiHandler = new APIHandler();
$missingConfig = checkConfig();
$apisByCategory = $apiHandler->getAllAPIs();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eCourts API Tester - Dashboard</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .api-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .api-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .api-card-header {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
            padding: 15px;
            border-bottom: none;
        }
        
        .api-card-body {
            padding: 20px;
        }
        
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .stats-card {
            padding: 20px;
            border-radius: 15px;
            color: white;
            margin-bottom: 20px;
        }
        
        .stats-card.total { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stats-card.success { background: linear-gradient(135deg, #27ae60, #2ecc71); }
        .stats-card.warning { background: linear-gradient(135deg, #f39c12, #f1c40f); }
        .stats-card.danger { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        
        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
            background: rgba(255, 255, 255, 0.2);
        }
        
        .btn-api {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-api:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
            color: white;
        }
        
        .nav-tabs {
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .nav-tabs .nav-link {
            color: white;
            border: none;
            padding: 10px 20px;
            margin-right: 10px;
            border-radius: 10px 10px 0 0;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            height: 80px;
            margin-bottom: 10px;
        }
        
        .alert-config {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: rgba(44, 62, 80, 0.9);">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-gavel me-2"></i>
                eCourts API Tester
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="api-test.php">
                            <i class="fas fa-vial me-1"></i> Test APIs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-book me-1"></i> Documentation
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
    <div class="container mt-4">
        <?php if (!empty($missingConfig)): ?>
        <div class="alert alert-config">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Configuration Required</h4>
            <p>Please update the following configuration values in <code>config/config.php</code>:</p>
            <ul>
                <?php foreach ($missingConfig as $config): ?>
                <li><strong><?php echo $config; ?></strong></li>
                <?php endforeach; ?>
            </ul>
            <p class="mb-0">Without these values, the API tester will not function properly.</p>
        </div>
        <?php endif; ?>
        
        <!-- Header -->
        <div class="logo-container">
            <img src="https://cdn-icons-png.flaticon.com/512/2092/2092655.png" alt="eCourts Logo" class="logo">
            <h1 class="text-white mb-3">eCourts High Court API Tester</h1>
            <p class="lead text-white">Test all 24 eCourts APIs with a beautiful, intuitive interface</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="row mb-5">
            <div class="col-md-3">
                <div class="stats-card total glass-card">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">24</h3>
                            <p class="mb-0">Total APIs</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card success glass-card">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">6</h3>
                            <p class="mb-0">Categories</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card warning glass-card">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">AES-128</h3>
                            <p class="mb-0">Encryption</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card danger glass-card">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">Real-time</h3>
                            <p class="mb-0">Testing</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- API Categories Tabs -->
        <div class="glass-card p-4 mb-5">
            <h3 class="mb-4 text-dark">
                <i class="fas fa-th-large me-2"></i>API Categories
            </h3>
            
            <ul class="nav nav-tabs" id="categoryTabs">
                <?php 
                $first = true;
                foreach ($apisByCategory as $category => $apis): 
                ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                       data-bs-toggle="tab" 
                       href="#<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                        <?php echo $category; ?>
                        <span class="badge bg-light text-dark ms-1"><?php echo count($apis); ?></span>
                    </a>
                </li>
                <?php 
                $first = false;
                endforeach; 
                ?>
            </ul>
            
            <div class="tab-content mt-4">
                <?php 
                $first = true;
                foreach ($apisByCategory as $category => $apis): 
                ?>
                <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                     id="<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                    <div class="row">
                        <?php foreach ($apis as $api): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="api-card glass-card">
                                <div class="position-relative">
                                    <div class="category-badge bg-<?php echo $this->getCategoryColor($category); ?> text-white">
                                        <?php echo $category; ?>
                                    </div>
                                </div>
                                <div class="api-card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-code me-2"></i>
                                        <?php echo $api['name']; ?>
                                    </h5>
                                </div>
                                <div class="api-card-body">
                                    <p class="text-muted mb-3">
                                        <?php echo $api['description']; ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-hashtag me-1"></i>
                                            ID: <?php echo $api['id']; ?>
                                        </small>
                                        <a href="api-test.php?api=<?php echo $api['id']; ?>" 
                                           class="btn btn-sm btn-api">
                                            <i class="fas fa-play me-1"></i> Test Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php 
                $first = false;
                endforeach; 
                ?>
            </div>
        </div>
        
        <!-- Quick Start Guide -->
        <div class="glass-card p-4 mb-5">
            <h3 class="text-dark mb-4">
                <i class="fas fa-rocket me-2"></i>Quick Start Guide
            </h3>
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-edit fa-2x"></i>
                        </div>
                        <h5 class="mt-3 text-dark">1. Configure</h5>
                        <p class="text-muted">Update config/config.php with your credentials</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center mb-3">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-check fa-2x"></i>
                        </div>
                        <h5 class="mt-3 text-dark">2. Select API</h5>
                        <p class="text-muted">Choose from 24 available APIs</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center mb-3">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-sliders-h fa-2x"></i>
                        </div>
                        <h5 class="mt-3 text-dark">3. Enter Parameters</h5>
                        <p class="text-muted">Fill in required fields for the API</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center mb-3">
                        <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-play fa-2x"></i>
                        </div>
                        <h5 class="mt-3 text-dark">4. Test & Analyze</h5>
                        <p class="text-muted">Run test and view detailed results</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-white text-center py-4 mt-5" style="background: rgba(44, 62, 80, 0.9);">
        <div class="container">
            <p class="mb-2">
                <i class="fas fa-gavel me-2"></i>
                eCourts API Tester v1.0
            </p>
            <p class="mb-0">
                Developed for High Courts API Specifications Version 1.0
                <br>
                <small>© 2024 National Informatics Centre, eCommittee, Supreme Court of India</small>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/script.js"></script>
    
    <script>
    // Helper function for category colors
    function getCategoryColor(category) {
        const colors = {
            'Overview': 'primary',
            'Case Search': 'success',
            'Causelist': 'warning',
            'Caveat Search': 'info',
            'Establishment': 'danger',
            'Master Data': 'secondary'
        };
        return colors[category] || 'dark';
    }
    
    // Initialize tooltips
    $(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
    </script>
</body>
</html>