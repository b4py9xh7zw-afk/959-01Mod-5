<?php
$pageTitle = '许可证详情 - 许可证管理平台';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            许可证详情
        </h1>
        <a href="/dashboard/licenses" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
            ← 返回许可证列表
        </a>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">许可证密钥</label>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <code class="text-lg font-mono text-gray-800"><?php echo htmlspecialchars($license['license_key']); ?></code>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">状态</label>
                    <div class="mt-2">
                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full <?php 
                            echo $license['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                ($license['status'] === 'expired' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'); 
                        ?>">
                            <?php 
                            echo $license['status'] === 'active' ? '活跃' : 
                                ($license['status'] === 'expired' ? '已过期' : '未激活'); 
                            ?>
                        </span>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">产品名称</label>
                    <p class="text-lg text-gray-800"><?php echo htmlspecialchars($license['product_name']); ?></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">分配用户</label>
                    <p class="text-lg text-gray-800"><?php echo htmlspecialchars($license['username'] ?? 'N/A'); ?></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($license['email'] ?? ''); ?></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">创建时间</label>
                    <p class="text-lg text-gray-800"><?php echo date('Y-m-d H:i:s', strtotime($license['created_at'])); ?></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">过期时间</label>
                    <p class="text-lg text-gray-800">
                        <?php echo $license['expires_at'] ? date('Y-m-d H:i:s', strtotime($license['expires_at'])) : '永不过期'; ?>
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">许可证类型</label>
                    <p class="text-lg text-gray-800">
                        <?php 
                        echo isset($license['license_type']) && $license['license_type'] === 'fixed' 
                            ? '固定授权' 
                            : '浮动授权'; 
                        ?>
                    </p>
                </div>
                
                <?php if (!isset($license['license_type']) || $license['license_type'] === 'floating'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">席位状态</label>
                    <div class="mt-2">
                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full <?php 
                            echo (isset($license['seat_status']) && $license['seat_status'] === 'borrowed') ? 'bg-yellow-100 text-yellow-800' : 
                                ((isset($license['seat_status']) && $license['seat_status'] === 'abnormal') ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'); 
                        ?>">
                            <?php 
                            echo (isset($license['seat_status']) && $license['seat_status'] === 'borrowed') ? '借出' : 
                                ((isset($license['seat_status']) && $license['seat_status'] === 'abnormal') ? '异常占用' : '空闲'); 
                            ?>
                        </span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!isset($license['license_type']) || $license['license_type'] === 'floating'): ?>
                <?php 
                $currentBorrow = null;
                if (class_exists('SeatBorrow')) {
                    $seatBorrowModel = new SeatBorrow();
                    $currentBorrow = $seatBorrowModel->getCurrentActiveBorrow($license['id']);
                }
                ?>
                <?php if ($currentBorrow): ?>
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">当前借用信息</h3>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">部门/项目：</span>
                            <span class="font-medium text-gray-800"><?php echo htmlspecialchars($currentBorrow['department_name']); ?></span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">借用人：</span>
                            <span class="font-medium text-gray-800"><?php echo htmlspecialchars($currentBorrow['borrower_name']); ?></span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">开始时间：</span>
                            <span class="font-medium text-gray-800"><?php echo date('Y-m-d H:i', strtotime($currentBorrow['start_date'])); ?></span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">到期时间：</span>
                            <span class="font-medium <?php echo strtotime($currentBorrow['end_date']) < time() ? 'text-red-600' : 'text-gray-800'; ?>">
                                <?php echo date('Y-m-d H:i', strtotime($currentBorrow['end_date'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-sm text-gray-500">用途：</span>
                        <p class="text-gray-800 mt-1"><?php echo htmlspecialchars($currentBorrow['purpose']); ?></p>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <a href="/seats/view?id=<?php echo $currentBorrow['id']; ?>" class="px-4 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition-colors">
                            查看详情
                        </a>
                    </div>
                </div>
            </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">管理员操作</h3>
                <div class="flex space-x-4">
                    <button 
                        onclick="document.getElementById('updateForm').classList.toggle('hidden')"
                        class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                    >
                        编辑许可证
                    </button>
                    <form method="POST" action="/licenses/delete" onsubmit="return confirm('确定要删除此许可证吗？');" class="inline">
                        <input type="hidden" name="id" value="<?php echo $license['id']; ?>">
                        <button 
                            type="submit"
                            class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                        >
                            删除许可证
                        </button>
                    </form>
                </div>
                
                <form id="updateForm" method="POST" action="/licenses/update" class="hidden mt-6 space-y-4 bg-gray-50 p-6 rounded-lg">
                    <input type="hidden" name="id" value="<?php echo $license['id']; ?>">
                    
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">产品名称</label>
                        <input 
                            type="text" 
                            id="product_name" 
                            name="product_name" 
                            value="<?php echo htmlspecialchars($license['product_name']); ?>"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">状态</label>
                        <select 
                            id="status" 
                            name="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="active" <?php echo $license['status'] === 'active' ? 'selected' : ''; ?>>活跃</option>
                            <option value="inactive" <?php echo $license['status'] === 'inactive' ? 'selected' : ''; ?>>未激活</option>
                            <option value="expired" <?php echo $license['status'] === 'expired' ? 'selected' : ''; ?>>已过期</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">过期时间</label>
                        <input 
                            type="date" 
                            id="expires_at" 
                            name="expires_at"
                            value="<?php echo $license['expires_at'] ? date('Y-m-d', strtotime($license['expires_at'])) : ''; ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label for="license_type_edit" class="block text-sm font-medium text-gray-700 mb-2">许可证类型</label>
                        <select 
                            id="license_type_edit" 
                            name="license_type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="toggleSeatStatusEdit()"
                        >
                            <option value="floating" <?php echo (isset($license['license_type']) && $license['license_type'] === 'floating') ? 'selected' : ''; ?>>浮动授权（可借用）</option>
                            <option value="fixed" <?php echo (isset($license['license_type']) && $license['license_type'] === 'fixed') ? 'selected' : ''; ?>>固定授权（不可借用）</option>
                        </select>
                    </div>
                    
                    <div id="seat_status_edit_div">
                        <label for="seat_status_edit" class="block text-sm font-medium text-gray-700 mb-2">席位状态</label>
                        <select 
                            id="seat_status_edit" 
                            name="seat_status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="idle" <?php echo (isset($license['seat_status']) && $license['seat_status'] === 'idle') ? 'selected' : ''; ?>>空闲</option>
                            <option value="borrowed" <?php echo (isset($license['seat_status']) && $license['seat_status'] === 'borrowed') ? 'selected' : ''; ?>>借出</option>
                            <option value="abnormal" <?php echo (isset($license['seat_status']) && $license['seat_status'] === 'abnormal') ? 'selected' : ''; ?>>异常占用</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button 
                            type="submit"
                            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            更新许可证
                        </button>
                        <button 
                            type="button"
                            onclick="document.getElementById('updateForm').classList.add('hidden')"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                        >
                            取消
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleSeatStatusEdit() {
    const licenseType = document.getElementById('license_type_edit').value;
    const seatStatusDiv = document.getElementById('seat_status_edit_div');
    const seatStatusSelect = document.getElementById('seat_status_edit');
    
    if (licenseType === 'fixed') {
        seatStatusDiv.classList.add('opacity-50');
        seatStatusSelect.value = 'idle';
        seatStatusSelect.disabled = true;
    } else {
        seatStatusDiv.classList.remove('opacity-50');
        seatStatusSelect.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    toggleSeatStatusEdit();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
