<?php
$pageTitle = '仪表板 - 许可证管理平台';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            仪表板
        </h1>
        <a href="/licenses/create" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
            + 创建许可证
        </a>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">许可证总数</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_licenses']; ?></p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">活跃许可证</p>
                    <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $stats['active_licenses']; ?></p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">已过期许可证</p>
                    <p class="text-3xl font-bold text-red-600 mt-2"><?php echo $stats['expired_licenses']; ?></p>
                </div>
                <div class="bg-red-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">用户总数</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2"><?php echo $stats['total_users']; ?></p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Seat Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="/seats/seats?seat_status=idle" class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">空闲席位</p>
                    <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $seatStats['idle_count'] ?? 0; ?></p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            <?php if ($seatStats['total_floating'] > 0): ?>
                <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full transition-all" style="width: <?php echo round(($seatStats['idle_count'] / $seatStats['total_floating']) * 100); ?>%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">占浮动席位 <?php echo round(($seatStats['idle_count'] / $seatStats['total_floating']) * 100); ?>%</p>
            <?php endif; ?>
        </a>

        <a href="/seats/seats?seat_status=borrowed" class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">借出席位</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2"><?php echo $seatStats['borrowed_count'] ?? 0; ?></p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <?php if ($seatStats['total_floating'] > 0): ?>
                <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full transition-all" style="width: <?php echo round(($seatStats['borrowed_count'] / $seatStats['total_floating']) * 100); ?>%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">占浮动席位 <?php echo round(($seatStats['borrowed_count'] / $seatStats['total_floating']) * 100); ?>%</p>
            <?php endif; ?>
        </a>

        <a href="/seats/seats?seat_status=abnormal" class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all hover:scale-105 <?php echo ($seatStats['abnormal_count'] ?? 0) > 0 ? 'ring-2 ring-red-500 ring-opacity-50' : ''; ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">异常占用</p>
                    <p class="text-3xl font-bold text-red-600 mt-2"><?php echo $seatStats['abnormal_count'] ?? 0; ?></p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
            <?php if ($seatStats['total_floating'] > 0): ?>
                <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full transition-all" style="width: <?php echo round(($seatStats['abnormal_count'] / $seatStats['total_floating']) * 100); ?>%"></div>
                </div>
                <p class="text-xs text-red-500 mt-2">需及时处理</p>
            <?php endif; ?>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php if ($_SESSION['role'] === 'admin' && !empty($pendingApprovals)): ?>
        <div class="bg-white rounded-xl shadow-lg border border-gray-100">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">待审批申请</h2>
                <a href="/seats?status=pending" class="text-sm text-blue-600 hover:text-blue-800">查看全部</a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php foreach ($pendingApprovals as $approval): ?>
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($approval['product_name']); ?></p>
                                <p class="text-sm text-gray-500 mt-1">
                                    <?php echo htmlspecialchars($approval['department_name']); ?> - 
                                    <?php echo htmlspecialchars($approval['borrower_name']); ?>
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    <?php echo date('Y-m-d', strtotime($approval['start_date'])); ?> 至 
                                    <?php echo date('Y-m-d', strtotime($approval['end_date'])); ?>
                                </p>
                            </div>
                            <div class="flex space-x-2 ml-4">
                                <button onclick="approveBorrow(<?php echo $approval['id']; ?>)" class="px-3 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600 transition-colors">批准</button>
                                <button onclick="rejectBorrow(<?php echo $approval['id']; ?>)" class="px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition-colors">拒绝</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($myBorrows)): ?>
        <div class="bg-white rounded-xl shadow-lg border border-gray-100">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">我的借用</h2>
                <a href="/seats" class="text-sm text-blue-600 hover:text-blue-800">查看全部</a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php foreach ($myBorrows as $borrow): ?>
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($borrow['product_name']); ?></p>
                                <p class="text-sm text-gray-500 mt-1">
                                    <?php echo htmlspecialchars($borrow['department_name']); ?>
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    <?php echo date('Y-m-d', strtotime($borrow['start_date'])); ?> 至 
                                    <?php echo date('Y-m-d', strtotime($borrow['end_date'])); ?>
                                </p>
                            </div>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                echo $borrow['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                    ($borrow['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($borrow['status'] === 'renew_pending' ? 'bg-orange-100 text-orange-800' : 
                                    ($borrow['status'] === 'expired' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')));
                            ?>">
                                <?php 
                                echo $borrow['status'] === 'active' ? '使用中' : 
                                    ($borrow['status'] === 'pending' ? '待审批' : 
                                    ($borrow['status'] === 'renew_pending' ? '续借待批' : 
                                    ($borrow['status'] === 'expired' ? '已过期' : 
                                    ($borrow['status'] === 'returned' ? '已归还' : $borrow['status']))));
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Licenses -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">最近许可证</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">许可证密钥</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">产品名称</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">用户</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">过期时间</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($recentLicenses)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">暂无许可证</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentLicenses as $license): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-sm font-mono text-gray-800"><?php echo htmlspecialchars($license['license_key']); ?></code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900"><?php echo htmlspecialchars($license['product_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($license['username'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                        echo $license['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                            ($license['status'] === 'expired' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'); 
                                    ?>">
                                        <?php 
                                        echo $license['status'] === 'active' ? '活跃' : 
                                            ($license['status'] === 'expired' ? '已过期' : '未激活'); 
                                        ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    <?php echo $license['expires_at'] ? date('Y-m-d', strtotime($license['expires_at'])) : '永不过期'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="/licenses/view?id=<?php echo $license['id']; ?>" class="text-blue-600 hover:text-blue-900">查看</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
