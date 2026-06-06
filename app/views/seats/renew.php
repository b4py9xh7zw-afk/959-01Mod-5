<?php
$pageTitle = '申请续借 - 许可证管理平台';
require_once __DIR__ . '/../layouts/header.php';

$isOverdue = strtotime($borrow['end_date']) < time();
?>

<div class="max-w-3xl mx-auto">
    <div class="space-y-8">
        <div class="flex items-center">
            <a href="/seats/view?id=<?php echo $borrow['id']; ?>" class="mr-4 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                申请续借
            </h1>
        </div>

        <?php if ($isOverdue): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div class="text-sm text-red-700">
                        <p class="font-medium">注意：此借用已逾期</p>
                        <p class="mt-1">当前借用已超过约定的结束时间，请及时说明业务原因并申请续借。</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">当前借用信息</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">许可证：</span>
                    <span class="font-medium text-gray-900"><?php echo htmlspecialchars($borrow['product_name']); ?></span>
                </div>
                <div>
                    <span class="text-gray-500">部门/项目：</span>
                    <span class="font-medium text-gray-900"><?php echo htmlspecialchars($borrow['department_name']); ?></span>
                </div>
                <div>
                    <span class="text-gray-500">原开始时间：</span>
                    <span class="font-medium text-gray-900"><?php echo date('Y-m-d H:i', strtotime($borrow['start_date'])); ?></span>
                </div>
                <div>
                    <span class="text-gray-500">原结束时间：</span>
                    <span class="font-medium <?php echo $isOverdue ? 'text-red-600' : 'text-gray-900'; ?>">
                        <?php echo date('Y-m-d H:i', strtotime($borrow['end_date'])); ?>
                    </span>
                </div>
                <div>
                    <span class="text-gray-500">原用途：</span>
                    <span class="font-medium text-gray-900"><?php echo htmlspecialchars(mb_substr($borrow['purpose'], 0, 30)); ?>...</span>
                </div>
                <div>
                    <span class="text-gray-500">已续借：</span>
                    <span class="font-medium text-gray-900"><?php echo $borrow['renew_count']; ?> 次</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
            <form method="POST" action="/seats/renew?id=<?php echo $borrow['id']; ?>" class="space-y-6">
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                        新的结束时间 <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" id="end_date" name="end_date" required
                        value="<?php echo date('Y-m-d\TH:i', strtotime($borrow['end_date'] . ' +7 days')); ?>"
                        min="<?php echo date('Y-m-d\TH:i', strtotime($borrow['end_date'] . ' +1 minute')); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <p class="mt-1 text-sm text-gray-500">新的结束时间必须晚于原结束时间</p>
                </div>

                <div>
                    <label for="business_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        业务原因 <span class="text-red-500">*</span>
                    </label>
                    <textarea id="business_reason" name="business_reason" rows="5" required
                        placeholder="请详细说明需要续借的业务原因，包括项目进展情况、延期原因、预计完成时间等..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"></textarea>
                    <p class="mt-1 text-sm text-gray-500">请提供充分的业务理由，以便管理员审批</p>
                </div>

                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-orange-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-orange-700">
                            <p class="font-medium">续借须知</p>
                            <ul class="mt-1 list-disc list-inside space-y-1">
                                <li>续借申请需要管理员审批（管理员操作除外）</li>
                                <li>续借期间席位仍由您独占使用</li>
                                <li>如续借申请被拒绝，将沿用原结束时间</li>
                                <li>到期后仍未归还将被标记为异常占用</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <a href="/seats/view?id=<?php echo $borrow['id']; ?>" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        取消
                    </a>
                    <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
                        <?php echo $_SESSION['role'] === 'admin' ? '确认续借' : '提交续借申请'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const endDate = document.getElementById('end_date');
    const businessReason = document.getElementById('business_reason');

    form.addEventListener('submit', function(e) {
        const reason = businessReason.value.trim();
        if (reason.length < 20) {
            e.preventDefault();
            alert('业务原因描述过短，请详细说明续借理由（至少20字）');
            return;
        }

        const end = new Date(endDate.value);
        const originalEnd = new Date('<?php echo $borrow['end_date']; ?>');
        const days = (end - originalEnd) / (1000 * 60 * 60 * 24);
        
        if (days > 180) {
            if (!confirm('续借期限超过6个月，是否确认提交？')) {
                e.preventDefault();
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
