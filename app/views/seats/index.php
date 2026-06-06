<?php
$pageTitle = '席位借用管理 - 许可证管理平台';
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
?>

<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            席位借用管理
        </h1>
        <div class="flex space-x-4">
            <a href="/seats/seats" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-all">
                席位状态
            </a>
            <a href="/seats/create" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
                + 申请借用
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">总浮动席位</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $seatStats['total_floating'] ?? 0; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">空闲席位</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $seatStats['idle_count'] ?? 0; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">借出席位</p>
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $seatStats['borrowed_count'] ?? 0; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-full">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">异常占用</p>
                    <p class="text-3xl font-bold text-red-600"><?php echo $seatStats['abnormal_count'] ?? 0; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4">
        <div class="flex flex-wrap gap-2">
            <a href="/seats" class="px-4 py-2 <?php echo !$status ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                全部
            </a>
            <a href="/seats?status=pending" class="px-4 py-2 <?php echo $status === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                待审批
            </a>
            <a href="/seats?status=active" class="px-4 py-2 <?php echo $status === 'active' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                使用中
            </a>
            <a href="/seats?status=renew_pending" class="px-4 py-2 <?php echo $status === 'renew_pending' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                续借待批
            </a>
            <a href="/seats?status=expired" class="px-4 py-2 <?php echo $status === 'expired' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                已过期
            </a>
            <a href="/seats?status=returned" class="px-4 py-2 <?php echo $status === 'returned' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                已归还
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">许可证</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">部门/项目</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">借用人</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">使用用途</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">借用期限</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($borrows)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                暂无借用记录
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($borrows as $borrow): ?>
                            <?php 
                            $statusConfig = $statusLabels[$borrow['status']] ?? ['text' => $borrow['status'], 'class' => 'bg-gray-100 text-gray-800'];
                            $isOverdue = $borrow['status'] === 'active' && strtotime($borrow['end_date']) < time();
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors <?php echo $isOverdue ? 'bg-red-50' : ''; ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($borrow['product_name']); ?></div>
                                    <code class="text-xs font-mono text-gray-500"><?php echo htmlspecialchars($borrow['license_key']); ?></code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo htmlspecialchars($borrow['department_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo htmlspecialchars($borrow['borrower_name']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                    <?php echo htmlspecialchars(mb_substr($borrow['purpose'], 0, 30)); ?>
                                    <?php echo mb_strlen($borrow['purpose']) > 30 ? '...' : ''; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <div><?php echo date('Y-m-d', strtotime($borrow['start_date'])); ?></div>
                                    <div class="text-xs text-gray-400">至 <?php echo date('Y-m-d', strtotime($borrow['end_date'])); ?></div>
                                    <?php if ($isOverdue): ?>
                                        <div class="text-xs text-red-500 font-medium mt-1">已逾期</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusConfig['class']; ?>">
                                        <?php echo $statusConfig['text']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="/seats/view?id=<?php echo $borrow['id']; ?>" class="text-blue-600 hover:text-blue-900">详情</a>
                                    <?php if ($_SESSION['role'] === 'admin' && $borrow['status'] === 'pending'): ?>
                                        <span class="text-gray-300">|</span>
                                        <button onclick="approveBorrow(<?php echo $borrow['id']; ?>)" class="text-green-600 hover:text-green-900">批准</button>
                                        <button onclick="rejectBorrow(<?php echo $borrow['id']; ?>)" class="text-red-600 hover:text-red-900">拒绝</button>
                                    <?php endif; ?>
                                    <?php if ($_SESSION['role'] === 'admin' && $borrow['status'] === 'renew_pending'): ?>
                                        <span class="text-gray-300">|</span>
                                        <button onclick="approveRenew(<?php echo $borrow['id']; ?>)" class="text-green-600 hover:text-green-900">批准续借</button>
                                        <button onclick="rejectRenew(<?php echo $borrow['id']; ?>)" class="text-red-600 hover:text-red-900">拒绝续借</button>
                                    <?php endif; ?>
                                    <?php if (in_array($borrow['status'], ['active', 'expired']) && ($borrow['borrower_id'] == $_SESSION['user_id'] || $_SESSION['role'] === 'admin')): ?>
                                        <span class="text-gray-300">|</span>
                                        <button onclick="returnSeat(<?php echo $borrow['id']; ?>)" class="text-purple-600 hover:text-purple-900">归还</button>
                                    <?php endif; ?>
                                    <?php if (in_array($borrow['status'], ['active', 'renew_pending']) && ($borrow['borrower_id'] == $_SESSION['user_id'] || $_SESSION['role'] === 'admin')): ?>
                                        <span class="text-gray-300">|</span>
                                        <a href="/seats/renew?id=<?php echo $borrow['id']; ?>" class="text-orange-600 hover:text-orange-900">续借</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    第 <?php echo $page; ?> 页，共 <?php echo $totalPages; ?> 页
                </div>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $status ? '&status=' . $status : ''; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">上一页</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $status ? '&status=' . $status : ''; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">下一页</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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
