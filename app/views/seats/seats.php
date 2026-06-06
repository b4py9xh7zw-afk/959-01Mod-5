<?php
$pageTitle = '席位状态管理 - 许可证管理平台';
require_once __DIR__ . '/../layouts/header.php';

$seatStatusLabels = [
    'idle' => ['text' => '空闲', 'class' => 'bg-green-100 text-green-800'],
    'borrowed' => ['text' => '借出', 'class' => 'bg-yellow-100 text-yellow-800'],
    'abnormal' => ['text' => '异常占用', 'class' => 'bg-red-100 text-red-800'],
];

$licenseTypeLabels = [
    'floating' => '浮动授权',
    'fixed' => '固定授权',
];
?>

<div class="space-y-8">
    <div class="flex justify-between items-center">
        <div class="flex items-center">
            <a href="/seats" class="mr-4 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                席位状态管理
            </h1>
        </div>
        <div class="flex space-x-4">
            <a href="/seats" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-all">
                借用记录
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
            <?php if ($seatStats['total_floating'] > 0): ?>
                <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: 100%"></div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 cursor-pointer hover:shadow-xl transition-shadow" onclick="window.location.href='/seats/seats?seat_status=idle'">
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
            <?php if ($seatStats['total_floating'] > 0): ?>
                <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: <?php echo round(($seatStats['idle_count'] / $seatStats['total_floating']) * 100); ?>%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2"><?php echo round(($seatStats['idle_count'] / $seatStats['total_floating']) * 100); ?>% 可用</p>
            <?php endif; ?>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 cursor-pointer hover:shadow-xl transition-shadow" onclick="window.location.href='/seats/seats?seat_status=borrowed'">
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
            <?php if ($seatStats['total_floating'] > 0): ?>
                <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: <?php echo round(($seatStats['borrowed_count'] / $seatStats['total_floating']) * 100); ?>%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2"><?php echo round(($seatStats['borrowed_count'] / $seatStats['total_floating']) * 100); ?>% 使用率</p>
            <?php endif; ?>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 cursor-pointer hover:shadow-xl transition-shadow" onclick="window.location.href='/seats/seats?seat_status=abnormal'">
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
            <?php if ($seatStats['total_floating'] > 0): ?>
                <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full" style="width: <?php echo round(($seatStats['abnormal_count'] / $seatStats['total_floating']) * 100); ?>%"></div>
                </div>
                <p class="text-xs text-red-500 mt-2">需及时处理</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap gap-2">
                <a href="/seats/seats" class="px-4 py-2 <?php echo !$seatStatus ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                    全部
                </a>
                <a href="/seats/seats?seat_status=idle" class="px-4 py-2 <?php echo $seatStatus === 'idle' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                    空闲
                </a>
                <a href="/seats/seats?seat_status=borrowed" class="px-4 py-2 <?php echo $seatStatus === 'borrowed' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                    借出
                </a>
                <a href="/seats/seats?seat_status=abnormal" class="px-4 py-2 <?php echo $seatStatus === 'abnormal' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                    异常占用
                </a>
            </div>
            <div class="text-sm text-gray-500">
                共 <?php echo $total; ?> 个浮动席位
                <?php if ($seatStatus): ?>
                    ，其中 <?php echo $seatStatusLabels[$seatStatus]['text']; ?> 的有 <?php echo count($licenses); ?> 个
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">许可证密钥</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">产品名称</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">席位状态</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">使用部门</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">借用人</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">到期时间</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($licenses)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                暂无席位数据
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($licenses as $license): ?>
                            <?php 
                            $seatStatusConfig = $seatStatusLabels[$license['seat_status']] ?? ['text' => $license['seat_status'], 'class' => 'bg-gray-100 text-gray-800'];
                            $isBorrowed = $license['seat_status'] === 'borrowed' || $license['seat_status'] === 'abnormal';
                            $isOverdue = $isBorrowed && $license['borrow_end_date'] && strtotime($license['borrow_end_date']) < time();
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors <?php echo $license['seat_status'] === 'abnormal' ? 'bg-red-50' : ''; ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-sm font-mono text-gray-800"><?php echo htmlspecialchars($license['license_key']); ?></code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium">
                                    <?php echo htmlspecialchars($license['product_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $seatStatusConfig['class']; ?>">
                                        <?php echo $seatStatusConfig['text']; ?>
                                    </span>
                                    <?php if ($isOverdue): ?>
                                        <div class="text-xs text-red-500 font-medium mt-1">已逾期</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    <?php if ($isBorrowed && !empty($license['department_name'])): ?>
                                        <?php echo htmlspecialchars($license['department_name']); ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    <?php if ($isBorrowed && !empty($license['borrower_name'])): ?>
                                        <?php echo htmlspecialchars($license['borrower_name']); ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    <?php if ($isBorrowed && !empty($license['borrow_end_date'])): ?>
                                        <span class="<?php echo $isOverdue ? 'text-red-600 font-medium' : ''; ?>">
                                            <?php echo date('Y-m-d H:i', strtotime($license['borrow_end_date'])); ?>
                                        </span>
                                        <?php if ($license['borrow_status'] === 'renew_pending'): ?>
                                            <div class="text-xs text-orange-500 mt-1">续借待审批</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="/licenses/view?id=<?php echo $license['id']; ?>" class="text-blue-600 hover:text-blue-900">查看</a>
                                    <?php if ($license['seat_status'] === 'idle' && $_SESSION['role'] === 'admin'): ?>
                                        <span class="text-gray-300">|</span>
                                        <a href="/seats/create?license_id=<?php echo $license['id']; ?>" class="text-green-600 hover:text-green-900">借出</a>
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
                        <a href="?page=<?php echo $page - 1; ?><?php echo $seatStatus ? '&seat_status=' . $seatStatus : ''; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">上一页</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $seatStatus ? '&seat_status=' . $seatStatus : ''; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">下一页</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
