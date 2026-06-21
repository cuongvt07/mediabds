<div class="user-page">
    <div class="site-shell user-form-wrap">
        <div class="user-form-head">
            <h1>{{ $editingId ? 'Sửa tin đăng' : 'Đăng tin cho thuê phòng' }}</h1>
            <a class="user-form-back" href="{{ route('user.dashboard') }}">← Về trang cá nhân</a>
        </div>

        <form wire:submit="save" class="user-form">
            <section class="user-form-card">
                <h2>Thông tin cơ bản</h2>
                <div class="user-form-grid">
                    <label class="ff full"><span>Tiêu đề tin <i>*</i></span>
                        <input type="text" wire:model="title" placeholder="VD: Studio đầy đủ nội thất gần ĐH Bách Khoa">
                        @error('title') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                    <label class="ff"><span>Số điện thoại liên hệ <i>*</i></span>
                        <input type="text" wire:model="contactPhone" placeholder="098...">
                        @error('contactPhone') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                    <label class="ff"><span>Giá thuê (đồng/tháng) <i>*</i></span>
                        <input type="number" min="0" step="1" wire:model="price" placeholder="VD: 3500000">
                        @error('price') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                    <label class="ff"><span>Loại tin <i>*</i></span>
                        <select wire:model.live="propertyType">
                            @foreach($propertyTypes as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                        </select>
                        @error('propertyType') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                    <label class="ff"><span>Dạng phòng <i>*</i></span>
                        <select wire:model="roomType">
                            @foreach($roomTypes as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                        </select>
                    </label>
                    <label class="ff"><span>Nội thất</span>
                        <select wire:model="furnish">
                            @foreach($furnishTypes as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="user-form-card">
                <h2>Địa chỉ</h2>
                <div class="user-form-grid">
                    <label class="ff"><span>Tỉnh / Thành phố <i>*</i></span>
                        <select wire:model.live="provinceId">
                            <option value="">Chọn tỉnh / thành</option>
                            @foreach($provinces as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                        </select>
                        @error('provinceId') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                    <label class="ff"><span>Quận / Huyện <i>*</i></span>
                        <select wire:model.live="districtId">
                            <option value="">Chọn quận / huyện</option>
                            @foreach($districts as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                        </select>
                        @error('districtId') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                    <label class="ff"><span>Phường / Xã <i>*</i></span>
                        <select wire:model="wardId">
                            <option value="">Chọn phường / xã</option>
                            @foreach($wards as $id => $name)<option value="{{ $id }}">{{ is_array($name) ? ($name['name'] ?? $id) : $name }}</option>@endforeach
                        </select>
                        @error('wardId') <em class="ff-err">{{ $message }}</em> @enderror
                    </label>
                </div>
            </section>

            <section class="user-form-card">
                <h2>Chi tiết phòng</h2>
                <div class="user-form-grid">
                    <div class="ff full"><span style="color:#8a6a00;">Tất cả những chi tiết phòng không bắt buộc điền. Nếu bỏ trống, hệ thống mặc định hiển thị là "Liên hệ".</span></div>
                    @if((int) $propertyType === 103)
                        <label class="ff"><span>Tên chung cư</span><input type="text" wire:model="apartmentName" placeholder="VD: Vinhomes Grand Park"></label>
                        <label class="ff"><span>Tên block</span><input type="text" wire:model="apartmentBlock" placeholder="VD: S1.02"></label>
                    @endif
                    @if((int) $propertyType === 108)
                        <label class="ff"><span>Số tầng</span><input type="number" min="0" step="1" wire:model="floors"></label>
                    @endif
                    @if(in_array((int) $propertyType, [103, 108], true))
                        <label class="ff"><span>Diện tích (m2)</span><input type="number" min="0" step="0.1" wire:model="area"></label>
                        <label class="ff"><span>Số tháng cọc</span><input type="number" min="0" step="1" wire:model="depositMonths"></label>
                    @endif
                    <label class="ff"><span>Phòng ngủ</span><input type="number" min="0" wire:model="bedrooms"></label>
                    <label class="ff"><span>Toilet</span><input type="number" min="0" wire:model="toilets"></label>
                    <label class="ff"><span>Tiền điện</span><input type="number" min="0" step="1" wire:model="electricity" placeholder="VD: 3500"></label>
                    <label class="ff"><span>Tiền nước</span><input type="number" min="0" step="1" wire:model="water" placeholder="VD: 100000"></label>
                    <label class="ff"><span>Phí giữ xe</span><input type="number" min="0" step="1" wire:model="parkingFee" placeholder="VD: 150000"></label>
                    <label class="ff"><span>Giờ giấc</span><select wire:model="accessHours">@foreach($accessHourOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></label>
                    <label class="ff"><span>Cửa sổ</span><select wire:model="window">@foreach($conditionOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></label>
                    <label class="ff"><span>Thú cưng</span><select wire:model="pets">@foreach($conditionOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></label>
                    <label class="ff"><span>Để xe</span><select wire:model="parking">@foreach($conditionOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></label>
                </div>
            </section>

            <section class="user-form-card">
                <h2>Tiện ích &amp; nội thất</h2>
                <div class="user-amenities">
                    @foreach($amenityItems as $item)
                        <label class="user-amenity">
                            <input type="checkbox" wire:model="amenities" value="{{ $item->key }}">
                            <span>{{ $item->name }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="user-form-card">
                <h2>Hình ảnh &amp; mô tả</h2>
                <div class="ff full"><span>Ảnh chính (bìa)</span>
                    <x-image-uploader name="avatarFile" :images="$avatar ? [$avatar] : []" :previews="$avatarFile ? [$avatarFile] : []" on-remove="removeAvatar" :multiple="false" label="Tải ảnh chính" hint="1 ảnh đại diện — hiển thị ở thẻ tin & ảnh bìa" />
                    @error('avatarFile') <em class="ff-err">{{ $message }}</em> @enderror
                </div>
                <div class="ff full"><span>Ảnh slider (bộ ảnh chi tiết)</span>
                    <x-image-uploader name="imageFiles" :images="$images" :previews="$imageFiles" on-remove="removeImage" label="Tải ảnh slider" hint="Nhiều ảnh — hiển thị ở slider trang chi tiết" />
                    @error('imageFiles.*') <em class="ff-err">{{ $message }}</em> @enderror
                </div>
                <label class="ff full"><span>Mô tả chi tiết</span>
                    <textarea wire:model="description" rows="5" placeholder="Mô tả phòng, vị trí, tiện ích xung quanh..."></textarea>
                </label>
            </section>

            <section class="user-form-card">
                <h2>Video &amp; liên kết (không bắt buộc)</h2>
                <div class="user-form-grid">
                    <label class="ff"><span>YouTube</span><input type="text" wire:model="youtubeLink" placeholder="https://..."></label>
                    <label class="ff"><span>Facebook</span><input type="text" wire:model="facebookLink" placeholder="https://..."></label>
                    <label class="ff"><span>TikTok</span><input type="text" wire:model="tiktokLink" placeholder="https://..."></label>
                    <label class="ff"><span>Google Map</span><input type="text" wire:model="googleMapLink" placeholder="https://..."></label>
                </div>
            </section>

            @if($errors->any())
                <div class="user-flash err">Vui lòng kiểm tra lại các trường còn thiếu hoặc sai.</div>
            @endif

            <div class="user-form-foot">
                <a class="user-form-cancel" href="{{ route('user.dashboard') }}">Hủy</a>
                <button type="submit" class="user-form-submit">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Lưu thay đổi' : 'Đăng tin ngay' }}</span>
                    <span wire:loading wire:target="save">Đang lưu...</span>
                </button>
            </div>
        </form>
    </div>
</div>
