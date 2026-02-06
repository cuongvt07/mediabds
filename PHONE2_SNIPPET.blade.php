<!-- Add this after Row 1 (Name + Phone) in the customer form -->

<!-- Row 1.5: Phone 2 (Optional) -->
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="fa-solid fa-phone text-gray-400 mr-1"></i>
            Số điện thoại 2
        </label>
        <input wire:model="phone2" type="text"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            placeholder="Nhập SĐT phụ (nếu có)">
        @error('phone2') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
    </div>
    <div></div>
</div>
