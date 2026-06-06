<?php
/**
 * SeatBorrow Controller - 席位借用管理控制器
 */

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/SeatBorrow.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/License.php';
require_once __DIR__ . '/../models/User.php';

class SeatBorrowController {
    private $authController;
    private $seatBorrowModel;
    private $departmentModel;
    private $licenseModel;
    private $userModel;
    
    public function __construct() {
        $this->authController = new AuthController();
        $this->seatBorrowModel = new SeatBorrow();
        $this->departmentModel = new Department();
        $this->licenseModel = new License();
        $this->userModel = new User();
    }
    
    public function index() {
        $this->authController->requireAuth();
        
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $status = $_GET['status'] ?? null;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        if ($role === 'admin') {
            $borrows = $this->seatBorrowModel->findAll($limit, $offset, $status);
            $total = $this->seatBorrowModel->count($status);
        } else {
            $borrows = $this->seatBorrowModel->findByBorrowerId($userId, $limit, $offset);
            $total = $this->seatBorrowModel->count();
        }
        
        $totalPages = ceil($total / $limit);
        $seatStats = $this->seatBorrowModel->getSeatStats();
        
        require_once __DIR__ . '/../views/seats/index.php';
    }
    
    public function create() {
        $this->authController->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $licenseId = $_POST['license_id'] ?? '';
            $departmentId = $_POST['department_id'] ?? '';
            $borrowerId = $_POST['borrower_id'] ?? $_SESSION['user_id'];
            $purpose = $_POST['purpose'] ?? '';
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            
            if (empty($licenseId) || empty($departmentId) || empty($purpose) || empty($startDate) || empty($endDate)) {
                $_SESSION['error'] = '请填写所有必填项';
                header('Location: /seats/create');
                exit;
            }
            
            if (strtotime($endDate) <= strtotime($startDate)) {
                $_SESSION['error'] = '结束时间必须晚于开始时间';
                header('Location: /seats/create');
                exit;
            }
            
            if (!$this->seatBorrowModel->isLicenseAvailable($licenseId, $startDate, $endDate)) {
                $_SESSION['error'] = '该席位在所选时间段内已被占用';
                header('Location: /seats/create');
                exit;
            }
            
            $approverId = $_SESSION['role'] === 'admin' ? $_SESSION['user_id'] : 0;
            $status = $_SESSION['role'] === 'admin' ? 'approved' : 'pending';
            
            try {
                $borrowId = $this->seatBorrowModel->create([
                    'license_id' => $licenseId,
                    'department_id' => $departmentId,
                    'borrower_id' => $borrowerId,
                    'approver_id' => $approverId,
                    'purpose' => $purpose,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $status
                ]);
                
                $_SESSION['success'] = $_SESSION['role'] === 'admin' 
                    ? '席位借用成功' 
                    : '借用申请已提交，等待审批';
                header('Location: /seats/view?id=' . $borrowId);
                exit;
            } catch (Exception $e) {
                error_log("Seat borrow creation error: " . $e->getMessage());
                $_SESSION['error'] = '创建借用申请失败，请重试';
                header('Location: /seats/create');
                exit;
            }
        }
        
        $departments = $this->departmentModel->findAll(100, 0);
        $users = $this->userModel->findAll(100, 0);
        
        $startDate = date('Y-m-d H:i:s');
        $endDate = date('Y-m-d H:i:s', strtotime('+7 days'));
        $availableLicenses = $this->seatBorrowModel->getAvailableFloatingLicenses($startDate, $endDate);
        
        require_once __DIR__ . '/../views/seats/create.php';
    }
    
    public function view() {
        $this->authController->requireAuth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '借用记录ID是必填项';
            header('Location: /seats');
            exit;
        }
        
        $borrow = $this->seatBorrowModel->findById($id);
        if (!$borrow) {
            $_SESSION['error'] = '借用记录不存在';
            header('Location: /seats');
            exit;
        }
        
        if ($borrow['borrower_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '访问被拒绝';
            header('Location: /seats');
            exit;
        }
        
        $history = $this->seatBorrowModel->getHistory($id);
        
        require_once __DIR__ . '/../views/seats/view.php';
    }
    
    public function approve() {
        $this->authController->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /seats');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '借用记录ID是必填项';
            header('Location: /seats');
            exit;
        }
        
        try {
            $this->seatBorrowModel->approve($id, $_SESSION['user_id']);
            $_SESSION['success'] = '借用申请已审批通过';
            header('Location: /seats/view?id=' . $id);
            exit;
        } catch (Exception $e) {
            error_log("Seat borrow approve error: " . $e->getMessage());
            $_SESSION['error'] = '审批失败：' . $e->getMessage();
            header('Location: /seats/view?id=' . $id);
            exit;
        }
    }
    
    public function reject() {
        $this->authController->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /seats');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        $reason = $_POST['reason'] ?? '';
        
        if (!$id) {
            $_SESSION['error'] = '借用记录ID是必填项';
            header('Location: /seats');
            exit;
        }
        
        try {
            $this->seatBorrowModel->reject($id, $_SESSION['user_id'], $reason);
            $_SESSION['success'] = '借用申请已拒绝';
            header('Location: /seats/view?id=' . $id);
            exit;
        } catch (Exception $e) {
            error_log("Seat borrow reject error: " . $e->getMessage());
            $_SESSION['error'] = '操作失败：' . $e->getMessage();
            header('Location: /seats/view?id=' . $id);
            exit;
        }
    }
    
    public function returnSeat() {
        $this->authController->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /seats');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '借用记录ID是必填项';
            header('Location: /seats');
            exit;
        }
        
        $borrow = $this->seatBorrowModel->findById($id);
        if (!$borrow) {
            $_SESSION['error'] = '借用记录不存在';
            header('Location: /seats');
            exit;
        }
        
        if ($borrow['borrower_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '无权限归还此席位';
            header('Location: /seats');
            exit;
        }
        
        try {
            $this->seatBorrowModel->returnSeat($id, $_SESSION['user_id']);
            $_SESSION['success'] = '席位归还成功';
            header('Location: /seats/view?id=' . $id);
            exit;
        } catch (Exception $e) {
            error_log("Seat return error: " . $e->getMessage());
            $_SESSION['error'] = '归还失败：' . $e->getMessage();
            header('Location: /seats/view?id=' . $id);
            exit;
        }
    }
    
    public function renew() {
        $this->authController->requireAuth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '借用记录ID是必填项';
            header('Location: /seats');
            exit;
        }
        
        $borrow = $this->seatBorrowModel->findById($id);
        if (!$borrow) {
            $_SESSION['error'] = '借用记录不存在';
            header('Location: /seats');
            exit;
        }
        
        if ($borrow['borrower_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '无权限续借此席位';
            header('Location: /seats');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newEndDate = $_POST['end_date'] ?? '';
            $businessReason = $_POST['business_reason'] ?? '';
            
            if (empty($newEndDate) || empty($businessReason)) {
                $_SESSION['error'] = '请填写新的结束时间和业务原因';
                header('Location: /seats/renew?id=' . $id);
                exit;
            }
            
            if (strtotime($newEndDate) <= strtotime($borrow['end_date'])) {
                $_SESSION['error'] = '新的结束时间必须晚于原结束时间';
                header('Location: /seats/renew?id=' . $id);
                exit;
            }
            
            if (!$this->seatBorrowModel->isLicenseAvailable($borrow['license_id'], $borrow['start_date'], $newEndDate, $id)) {
                $_SESSION['error'] = '该席位在续借时间段内已被其他申请占用';
                header('Location: /seats/renew?id=' . $id);
                exit;
            }
            
            try {
                $this->seatBorrowModel->requestRenew($id, $newEndDate, $businessReason, $_SESSION['user_id']);
                $_SESSION['success'] = '续借申请已提交' . ($_SESSION['role'] === 'admin' ? '' : '，等待审批');
                
                if ($_SESSION['role'] === 'admin') {
                    $this->seatBorrowModel->approveRenew($id, $_SESSION['user_id']);
                    $_SESSION['success'] = '续借成功';
                }
                
                header('Location: /seats/view?id=' . $id);
                exit;
            } catch (Exception $e) {
                error_log("Seat renew error: " . $e->getMessage());
                $_SESSION['error'] = '续借申请失败：' . $e->getMessage();
                header('Location: /seats/renew?id=' . $id);
                exit;
            }
        }
        
        require_once __DIR__ . '/../views/seats/renew.php';
    }
    
    public function approveRenew() {
        $this->authController->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /seats');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '借用记录ID是必填项';
            header('Location: /seats');
            exit;
        }
        
        try {
            $this->seatBorrowModel->approveRenew($id, $_SESSION['user_id']);
            $_SESSION['success'] = '续借申请已审批通过';
            header('Location: /seats/view?id=' . $id);
            exit;
        } catch (Exception $e) {
            error_log("Seat renew approve error: " . $e->getMessage());
            $_SESSION['error'] = '审批失败：' . $e->getMessage();
            header('Location: /seats/view?id=' . $id);
            exit;
        }
    }
    
    public function rejectRenew() {
        $this->authController->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /seats');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        $reason = $_POST['reason'] ?? '';
        
        if (!$id) {
            $_SESSION['error'] = '借用记录ID是必填项';
            header('Location: /seats');
            exit;
        }
        
        try {
            $this->seatBorrowModel->rejectRenew($id, $_SESSION['user_id'], $reason);
            $_SESSION['success'] = '续借申请已拒绝';
            header('Location: /seats/view?id=' . $id);
            exit;
        } catch (Exception $e) {
            error_log("Seat renew reject error: " . $e->getMessage());
            $_SESSION['error'] = '操作失败：' . $e->getMessage();
            header('Location: /seats/view?id=' . $id);
            exit;
        }
    }
    
    public function seats() {
        $this->authController->requireAuth();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $seatStatus = $_GET['seat_status'] ?? null;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        if ($seatStatus) {
            $licenses = $this->licenseModel->findBySeatStatus($seatStatus, $limit, $offset);
            $total = $this->licenseModel->countBySeatStatus($seatStatus);
        } else {
            $licenses = $this->licenseModel->findFloatingLicenses($limit, $offset);
            $total = $this->licenseModel->countByLicenseType('floating');
        }
        
        $totalPages = ceil($total / $limit);
        $seatStats = $this->seatBorrowModel->getSeatStats();
        
        require_once __DIR__ . '/../views/seats/seats.php';
    }
}
