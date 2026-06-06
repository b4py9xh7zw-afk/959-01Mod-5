<?php
$pageTitle = '申请借用席位 - 许可证管理平台';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="space-y-8">
        <div class="flex items-center">
            <a href="/seats" class="mr-4 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                申请借用席位
            </h1>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
            <form method="POST" action="/seats/create" class="space-y-6">
                <div>
                    <label for="license_id" class="block text-sm font-medium text-gray-700 mb-2">
                        选择许可证 <span class="text-red-500">*</span>
                    </label>
                    <select id="license_id" name="license_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">请选择可用的浮动许可证</option>
                        <?php if (empty($availableLicenses)): ?>
                            <option value="" disabled>暂无可用的浮动许可证</option>
                        <?php else: ?>
                            <?php foreach ($availableLicenses as $license): ?>
                                <option value="<?php echo $license['id']; ?>">
                                    <?php echo htmlspecialchars($license['product_name']); ?> - 
                                    <?php echo htmlspecialchars($license['license_key']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">仅显示当前可用的浮动授权许可证</p>
                </div>

                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">
                        部门/项目 <span class="text-red-500">*</span>
                    </label>
                    <select id="department_id" name="department_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">请选择部门或项目</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>">
                                <?php echo htmlspecialchars($dept['name']); ?>
                                <?php if ($dept['description']): ?>
                                    (<?php echo htmlspecialchars($dept['description']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <div>
                        <label for="borrower_id" class="block text-sm font-medium text-gray-700 mb-2">
                            借用人 <span class="text-red-500">*</span>
                        </label>
                        <select id="borrower_id" name="borrower_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo $user['id'] == $_SESSION['user_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">
                        使用用途 <span class="text-red-500">*</span>
                    </label>
                    <textarea id="purpose" name="purpose" rows="3" required
                        placeholder="请详细描述使用此席位的业务用途..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            开始时间 <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" id="start_date" name="start_date" required
                            value="<?php echo date('Y-m-d\TH:i'); ?>"
                            min="<?php echo date('Y-m-d\TH:i'); ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            结束时间 <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" id="end_date" name="end_date" required
                            value="<?php echo date('Y-m-d\TH:i', strtotime('+7 days')); ?>"
                            min="<?php echo date('Y-m-d\TH:i'); ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium">借用须知</p>
                            <ul class="mt-1 list-disc list-inside space-y-1">
                                <li>借用期间其他人不能抢占此席位</li>
                                <li>到期后系统将自动标记为异常占用，24小时宽限期后自动收回</li>
                                <li>如需延期，请提前申请续借并说明业务原因</li>
                                <?php if ($_SESSION['role'] !== 'admin'): ?>
                                    <li>您的申请需要管理员审批后才能生效</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <a href="/seats" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        取消
                    </a>
                    <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
                        <?php echo $_SESSION['role'] === 'admin' ? '确认借用' : '提交申请'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    startDate.addEventListener('change', function() {
        endDate.min = this.value;
        if (endDate.value && endDate.value <= this.value) {
            endDate.value = '';
        }
    });

    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        
        if (end <= start) {
            e.preventDefault();
            alert('结束时间必须晚于开始时间');
            return;
        }

        const days = (end - start) / (1000 * 60 * 60 * 24);
        if (days > 365) {
            if (!confirm('借用期限超过1年，是否确认提交？')) {
                e.preventDefault();
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
