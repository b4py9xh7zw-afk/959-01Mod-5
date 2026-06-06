<?php
$pageTitle = '借用详情 - 许可证管理平台';
require_once __DIR__ . '/../layouts/header.php';

$statusLabels = [
    'pending' => ['text' => '待审批', 'class' => 'bg-yellow-100 text-yellow-800'],
    'approved' => ['text' => '已批准', 'class' => 'bg-blue-100 text-blue-800'],
    'active' => ['text' => '使用中', 'class' => 'bg-green-100 text-green-800'],
    'returned' => ['text' => '已归还', 'class' => 'bg-gray-100 text-gray-800'],
    'expired' => ['text' => '已过期', 'class' => 'bg-red-100 text-red-800'],
    'rejected' => ['text' => '已拒绝', 'class' => 'bg-red-100 text-red-800'],
    'renew_pending' => ['text' => '续借待批', 'class' => 'bg-orange-100 text-orange-800'],
];

$seatStatusLabels = [
    'idle' => ['text' => '空闲', 'class' => 'bg-green-100 text-green-800'],
    'borrowed' => ['text' => '借出', 'class' => 'bg-yellow-100 text-yellow-800'],
    'abnormal' => ['text' => '异常占用', 'class' => 'bg-red-100 text-red-800'],
];

$statusConfig = $statusLabels[$borrow['status']] ?? ['text' => $borrow['status'], 'class' => 'bg-gray-100 text-gray-800'];
$seatStatusConfig = $seatStatusLabels[$borrow['seat_status']] ?? ['text' => $borrow['seat_status'], 'class' => 'bg-gray-100 text-gray-800'];

$isOverdue = $borrow['status'] === 'active' && strtotime($borrow['end_date']) < time();
?>

<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <a href="/seats" class="mr-4 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                借用详情
            </h1>
        </div>
        <div class="flex space-x-3">
            <?php if ($_SESSION['role'] === 'admin' && $borrow['status'] === 'pending'): ?>
                <button onclick="approveBorrow(<?php echo $borrow['id']; ?>)" 
                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                    批准申请
                </button>
                <button onclick="rejectBorrow(<?php echo $borrow['id']; ?>)" 
                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                    拒绝申请
                </button>
            <?php endif; ?>
            <?php if ($_SESSION['role'] === 'admin' && $borrow['status'] === 'renew_pending'): ?>
                <button onclick="approveRenew(<?php echo $borrow['id']; ?>)" 
                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                    批准续借
                </button>
                <button onclick="rejectRenew(<?php echo $borrow['id']; ?>)" 
                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                    拒绝续借
                </button>
            <?php endif; ?>
            <?php if (in_array($borrow['status'], ['active', 'expired']) && ($borrow['borrower_id'] == $_SESSION['user_id'] || $_SESSION['role'] === 'admin')): ?>
                <button onclick="returnSeat(<?php echo $borrow['id']; ?>)" 
                    class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                    归还席位
                </button>
            <?php endif; ?>
            <?php if (in_array($borrow['status'], ['active', 'renew_pending']) && ($borrow['borrower_id'] == $_SESSION['user_id'] || $_SESSION['role'] === 'admin')): ?>
                <a href="/seats/renew?id=<?php echo $borrow['id']; ?>" 
                    class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                    申请续借
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">基本信息</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">许可证产品</label>
                        <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($borrow['product_name']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">许可证密钥</label>
                        <code class="text-sm font-mono text-gray-700 bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($borrow['license_key']); ?></code>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">部门/项目</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($borrow['department_name']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">席位状态</label>
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $seatStatusConfig['class']; ?>">
                            <?php echo $seatStatusConfig['text']; ?>
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">借用人</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($borrow['borrower_name']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($borrow['borrower_email']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">审批人</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($borrow['approver_name'] ?? '待审批'); ?></p>
                        <?php if ($borrow['approver_email']): ?>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($borrow['approver_email']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">使用用途</h2>
                <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($borrow['purpose']); ?></p>
            </div>

            <?php if ($borrow['business_reason']): ?>
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">业务原因（续借）</h2>
                    <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($borrow['business_reason']); ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">操作历史</h2>
                <div class="space-y-4">
                    <?php if (empty($history)): ?>
                        <p class="text-gray-500 text-center py-4">暂无操作历史</p>
                    <?php else: ?>
                        <div class="relative">
                            <?php foreach ($history as $index => $record): ?>
                                <div class="flex items-start <?php echo $index < count($history) - 1 ? 'pb-6' : ''; ?>">
                                    <div class="flex flex-col items-center mr-4">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                            <?php if ($record['action'] === 'created'): ?>
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                            <?php elseif ($record['action'] === 'approved'): ?>
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            <?php elseif ($record['action'] === 'rejected'): ?>
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            <?php elseif ($record['action'] === 'returned'): ?>
                                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                </svg>
                                            <?php elseif ($record['action'] === 'renewed'): ?>
                                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                            <?php elseif ($record['action'] === 'expired'): ?>
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            <?php else: ?>
                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($index < count($history) - 1): ?>
                                            <div class="w-0.5 h-full bg-gray-200 mt-2"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($record['remark'] ?? $record['action']); ?>
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    操作人：<?php echo htmlspecialchars($record['operator_name'] ?? '系统'); ?>
                                                </p>
                                            </div>
                                            <span class="text-xs text-gray-400">
                                                <?php echo date('Y-m-d H:i', strtotime($record['created_at'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">状态信息</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">申请状态</span>
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusConfig['class']; ?>">
                            <?php echo $statusConfig['text']; ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">续借次数</span>
                        <span class="text-gray-900 font-medium"><?php echo $borrow['renew_count']; ?> 次</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">时间信息</h2>
                <div class="space-y-4">
                    <div>
                        <span class="text-sm text-gray-500">开始时间</span>
                        <p class="text-gray-900 font-medium"><?php echo date('Y-m-d H:i', strtotime($borrow['start_date'])); ?></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">预计结束时间</span>
                        <p class="text-gray-900 font-medium <?php echo $isOverdue ? 'text-red-600' : ''; ?>">
                            <?php echo date('Y-m-d H:i', strtotime($borrow['end_date'])); ?>
                        </p>
                        <?php if ($isOverdue): ?>
                            <p class="text-xs text-red-500 mt-1">已逾期</p>
                        <?php endif; ?>
                    </div>
                    <?php if ($borrow['actual_return_date']): ?>
                        <div>
                            <span class="text-sm text-gray-500">实际归还时间</span>
                            <p class="text-gray-900 font-medium"><?php echo date('Y-m-d H:i', strtotime($borrow['actual_return_date'])); ?></p>
                        </div>
                    <?php endif; ?>
                    <div>
                        <span class="text-sm text-gray-500">申请时间</span>
                        <p class="text-gray-900 font-medium"><?php echo date('Y-m-d H:i', strtotime($borrow['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <?php if ($borrow['status'] === 'active' || $borrow['status'] === 'renew_pending'): ?>
                <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <h3 class="text-lg font-semibold mb-2">剩余时间</h3>
                    <?php
                    $now = time();
                    $end = strtotime($borrow['end_date']);
                    $remaining = $end - $now;
                    
                    if ($remaining > 0) {
                        $days = floor($remaining / (60 * 60 * 24));
                        $hours = floor(($remaining % (60 * 60 * 24)) / (60 * 60));
                        $minutes = floor(($remaining % (60 * 60)) / 60);
                        echo '<div class="text-3xl font-bold">';
                        if ($days > 0) echo "{$days}天 ";
                        if ($hours > 0) echo "{$hours}时 ";
                        echo "{$minutes}分";
                        echo '</div>';
                    } else {
                        $overdue = $now - $end;
                        $days = floor($overdue / (60 * 60 * 24));
                        $hours = floor(($overdue % (60 * 60 * 24)) / (60 * 60));
                        echo '<div class="text-3xl font-bold text-yellow-300">';
                        echo "已逾期 {$days}天 {$hours}时";
                        echo '</div>';
                    }
                    ?>
                    <p class="text-blue-100 text-sm mt-2">距离结束时间</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function approveBorrow(id) {
    if (confirm('确定要批准此借用申请吗？')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/seats/approve';
        form.innerHTML = '<input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectBorrow(id) {
    const reason = prompt('请输入拒绝原因：');
    if (reason !== null) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/seats/reject';
        form.innerHTML = '<input type="hidden" name="id" value="' + id + '"><input type="hidden" name="reason" value="' + reason + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function returnSeat(id) {
    if (confirm('确定要归还此席位吗？')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/seats/return';
        form.innerHTML = '<input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function approveRenew(id) {
    if (confirm('确定要批准此续借申请吗？')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/seats/approve-renew';
        form.innerHTML = '<input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectRenew(id) {
    const reason = prompt('请输入拒绝续借的原因：');
    if (reason !== null) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/seats/reject-renew';
        form.innerHTML = '<input type="hidden" name="id" value="' + id + '"><input type="hidden" name="reason" value="' + reason + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
