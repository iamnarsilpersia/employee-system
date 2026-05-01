<?php
// Common functions for Employee Management System

// Redirect if not logged in
function require_admin_login() {
    if(strlen($_SESSION['alogin']) == 0) {
        header('location:index.php');
        exit;
    }
}

function require_employee_login() {
    if(strlen($_SESSION['emplogin']) == 0 && strlen($_SESSION['empid']) == 0) {
        header('location:login.php');
        exit;
    }
}

// Get employee by ID
function get_employee_by_id($dbh, $eid) {
    $sql = "SELECT * FROM tblemployees WHERE id=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
    $query->execute();
    return $query->fetch(PDO::FETCH_OBJ);
}

// Get all employees
function get_all_employees($dbh) {
    $sql = "SELECT EmpId,FirstName,LastName,Department,Status,RegDate,id FROM tblemployees";
    $query = $dbh->prepare($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

// Get all departments
function get_all_departments($dbh) {
    $sql = "SELECT DepartmentName FROM tbldepartments";
    $query = $dbh->prepare($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

// Toggle employee status
function toggle_employee_status($dbh, $id, $status) {
    $sql = "UPDATE tblemployees SET Status=:status WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_STR);
    $query->bindParam(':status', $status, PDO::PARAM_STR);
    return $query->execute();
}

// Delete employee
function delete_employee($dbh, $id) {
    $sql = "DELETE FROM tblemployees WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_STR);
    return $query->execute();
}

// Update employee password with hashing
function update_employee_password($dbh, $eid, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE tblemployees SET Password=:password WHERE id=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
    return $query->execute();
}

// Generate next employee ID
function generate_employee_id($dbh) {
    $sql = "SELECT MAX(CAST(SUBSTRING(EmpId, 4) AS UNSIGNED)) as max_id FROM tblemployees";
    $query = $dbh->prepare($sql);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    $next_id = $result['max_id'] ? $result['max_id'] + 1 : 1;
    return 'ASTR' . str_pad($next_id, 6, '0', STR_PAD_LEFT);
}

// Delete department
function delete_department($dbh, $id) {
    $sql = "DELETE FROM tbldepartments WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_STR);
    return $query->execute();
}

// Delete leave type
function delete_leave_type($dbh, $id) {
    $sql = "DELETE FROM tblleavetype WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_STR);
    return $query->execute();
}

// Delete admin
function delete_admin($dbh, $id) {
    $sql = "DELETE FROM admin WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_STR);
    return $query->execute();
}


