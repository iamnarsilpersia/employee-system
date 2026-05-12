<?php
session_start();
include('../includes/dbconn.php');
include('../includes/functions.php');

if(empty($_SESSION['alogin'])) {   
    header('location:../index.php');
    exit();
}

$page='payroll'; 
include('../includes/admin-header.php'); 
?>

    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">Deductions Settings</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="dashboard.php">Home</a></li>
                        <li><span>Deductions</span></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-sm-6 clearfix">
                <?php include '../includes/admin-profile-section.php'; ?>
            </div>
        </div>
    </div>

    <div class="main-content-inner">
        <div class="row">
            <div class="col-lg-8 col-md-10">
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Philippine Standard Deductions</h5>
                            <p class="mb-0">Deductions are now automatically calculated based on Philippine labor standards (TRAIN Law). Admin no longer needs to set percentage rates manually.</p>
                        </div>
                        
                        <h5 class="mt-4 mb-3">Current Contribution Rates</h5>
                        <table class="table table-bordered">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>Deduction Type</th>
                                    <th>Description</th>
                                    <th>Rate Applied</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>SSS</strong></td>
                                    <td>Social Security System Employee Contribution</td>
                                    <td>Based on monthly salary bracket</td>
                                </tr>
                                <tr>
                                    <td><strong>PhilHealth</strong></td>
                                    <td>National Health Insurance Program</td>
                                    <td>4.5% of monthly salary (max ₱4,500)</td>
                                </tr>
                                <tr>
                                    <td><strong>Pag-IBIG</strong></td>
                                    <td>Home Development Mutual Fund</td>
                                    <td>1% (≤₱1,500 salary) or 2% (≥₱1,500 salary), max ₱100</td>
                                </tr>
                                <tr>
                                    <td><strong>Withholding Tax</strong></td>
                                    <td>Income Tax (TRAIN Law Brackets)</td>
                                    <td>Based on annual taxable income</td>
                                </tr>
                            </tbody>
                        </table>

                        <h5 class="mt-4 mb-3">Tax Brackets (TRAIN Law)</h5>
                        <table class="table table-sm table-striped">
                            <thead class="bg-secondary text-white">
                                <tr>
                                    <th>Annual Taxable Income</th>
                                    <th>Tax Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>₱0 - ₱20,000</td><td>0%</td></tr>
                                <tr><td>₱20,001 - ₱30,000</td><td>20% of excess over ₱20,000</td></tr>
                                <tr><td>₱30,001 - ₱40,000</td><td>₱2,000 + 25% of excess</td></tr>
                                <tr><td>₱40,001 - ₱80,000</td><td>₱4,500 + 30% of excess</td></tr>
                                <tr><td>₱80,001 - ₱130,000</td><td>₱16,500 + 30% of excess</td></tr>
                                <tr><td>₱130,001 - ₱250,000</td><td>₱31,500 + 32% of excess</td></tr>
                                <tr><td>₱250,001 - ₱500,000</td><td>₱69,500 + 35% of excess</td></tr>
                                <tr><td>₱500,001+</td><td>₱157,500 + 35% of excess</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/admin-footer.php'; ?>