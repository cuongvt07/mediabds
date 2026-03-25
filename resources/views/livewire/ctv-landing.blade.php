<div class="ctv-luxury-page relative overflow-hidden py-6 md:py-10">
    <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-[#c2a56b]/20 blur-3xl"></div>
    <div class="pointer-events-none absolute top-1/3 -right-24 h-80 w-80 rounded-full bg-[#2a3959]/10 blur-3xl"></div>

    <section class="hero-shell relative overflow-hidden rounded-[28px] border border-[#e7dcc8] p-6 sm:p-8 md:p-12 reveal-up">
        <div class="absolute inset-0 hero-mesh opacity-80"></div>
        <div class="relative z-10 grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:gap-10">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-[#d6c4a1] bg-white/70 px-4 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.22em] text-[#7e6431]">
                    <i class="fa-solid fa-gem text-[11px]"></i>
                    Chương trình CTV bất động sản
                </div>

                <h1 class="mt-5 font-serif-luxury text-4xl leading-tight text-[#162238] sm:text-5xl md:text-6xl">
                    Hợp tác chuẩn
                    <span class="block text-[#9e7830]">dịch vụ cao cấp</span>
                    cho người làm BĐS
                </h1>

                <p class="mt-5 max-w-2xl text-sm leading-relaxed text-[#4a5872] sm:text-base">
                    Gia nhập hệ thống CTV VM PHÚ THỊNH LAND để khai thác nguồn hàng thật, quy trình rõ ràng,
                    hoa hồng minh bạch và được huấn luyện bán hàng bài bản theo tiêu chuẩn đội ngũ chuyên nghiệp.
                </p>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="/login?register=1"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#14233b] px-7 py-3.5 text-sm font-extrabold uppercase tracking-[0.14em] text-white transition-all hover:-translate-y-0.5 hover:bg-[#0f1a2d] hover:shadow-[0_16px_30px_-14px_rgba(18,35,59,0.55)]">
                        Đăng ký ngay <i class="fa-solid fa-arrow-right-long text-xs"></i>
                    </a>
                    <a href="#bang-vang"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-[#d7c6a6] bg-white/75 px-7 py-3.5 text-sm font-extrabold uppercase tracking-[0.14em] text-[#8a692f] transition-all hover:-translate-y-0.5 hover:bg-[#fff7ea]">
                        Xem bảng vinh danh
                    </a>
                </div>

                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    <div class="stat-pill">
                        <div class="stat-label">Tổng doanh thu</div>
                        <div class="stat-value">{{ number_format($stats['total_revenue'] / 1000000000, 1) }}B+</div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-label">Số CTV</div>
                        <div class="stat-value">{{ number_format($stats['total_ctvs']) }}+</div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-label">Giao dịch</div>
                        <div class="stat-value">{{ number_format($stats['total_deals']) }}+</div>
                    </div>
                </div>
            </div>

            <div class="reveal-up rounded-[26px] border border-[#e4d8c0] bg-white/75 p-6 shadow-[0_26px_50px_-35px_rgba(20,35,59,0.45)] backdrop-blur-sm md:p-7"
                style="animation-delay: .12s;">
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#8a692f]">Tiêu chuẩn đối tác</p>
                <h2 class="mt-3 font-serif-luxury text-3xl leading-tight text-[#14233b]">
                    Sàng lọc nghiêm túc,
                    <span class="text-[#9e7830]">đồng hành dài hạn</span>
                </h2>
                <div class="mt-5 space-y-3 text-sm text-[#4b5972]">
                    <div class="feature-line"><i class="fa-solid fa-check"></i> Nguồn hàng bộ lọc, cập nhật liên tục</div>
                    <div class="feature-line"><i class="fa-solid fa-check"></i> Dữ liệu hỗ trợ phân bổ theo khu vực</div>
                    <div class="feature-line"><i class="fa-solid fa-check"></i> Chính sách thưởng theo hiệu suất thực tế</div>
                    <div class="feature-line"><i class="fa-solid fa-check"></i> Báo cáo doanh thu theo thời gian thực</div>
                </div>
                <div class="mt-6 rounded-2xl border border-[#eadfc9] bg-[#fffaf1] p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#8e6c31]">Mục tiêu</p>
                    <p class="mt-2 text-sm text-[#52607a]">
                        Xây dựng mạng lưới CTV BĐS chuyên nghiệp, tăng tốc giao dịch và tăng thu nhập bền vững.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-10 grid gap-4 md:grid-cols-3">
        <article class="benefit-card reveal-up" style="animation-delay:.04s;">
            <div class="benefit-icon"><i class="fa-solid fa-building"></i></div>
            <h3 class="benefit-title">Sản phẩm có chọn lọc</h3>
            <p class="benefit-copy">Danh mục nhà đất được cập nhật, rõ pháp lý, dễ tư vấn và dễ chốt deal.</p>
        </article>
        <article class="benefit-card reveal-up" style="animation-delay:.1s;">
            <div class="benefit-icon"><i class="fa-solid fa-scale-balanced"></i></div>
            <h3 class="benefit-title">Chính sách minh bạch</h3>
            <p class="benefit-copy">Hoa hồng, thưởng và KPI được công khai. CTV theo dõi được mọi giai đoạn giao dịch.</p>
        </article>
        <article class="benefit-card reveal-up" style="animation-delay:.16s;">
            <div class="benefit-icon"><i class="fa-solid fa-user-tie"></i></div>
            <h3 class="benefit-title">Hỗ trợ từ đội ngũ senior</h3>
            <p class="benefit-copy">Được mentor bởi đội ngũ kinh doanh và vận hành để rút ngắn thời gian lên doanh số.</p>
        </article>
    </section>

    <section class="mt-10 rounded-[28px] border border-[#e8ddca] bg-white/80 p-6 sm:p-8 md:p-10 reveal-up" style="animation-delay:.2s;">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#8a692f]">Quy trình hợp tác</p>
                <h2 class="mt-2 font-serif-luxury text-3xl text-[#14233b] sm:text-4xl">3 bước để vào guồng quay giao dịch</h2>
            </div>
            <a href="/login?register=1" class="text-xs font-black uppercase tracking-[0.14em] text-[#8a692f] hover:text-[#6e5121]">
                Đăng ký và kích hoạt tài khoản
            </a>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="step-box">
                <div class="step-no">01</div>
                <h3>Xác minh thông tin</h3>
                <p>Hoàn tất đăng ký, nhận mã mời và kích hoạt hồ sơ CTV.</p>
            </div>
            <div class="step-box">
                <div class="step-no">02</div>
                <h3>Đào tạo - setup khu vực</h3>
                <p>Onboarding quy trình chốt deal, được gán nguồn hàng theo khu vực phù hợp.</p>
            </div>
            <div class="step-box">
                <div class="step-no">03</div>
                <h3>Bán hàng - nhận thưởng</h3>
                <p>Theo dõi doanh số theo thời gian thực, đổi thưởng và nâng hạng CTV.</p>
            </div>
        </div>
    </section>

    <section id="bang-vang" class="mt-10 rounded-[30px] border border-[#e5d9c3] bg-[#162338] p-6 sm:p-8 md:p-10 text-white reveal-up"
        style="animation-delay:.26s;">
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#d7c090]">Bảng vàng CTV</p>
                <h2 class="mt-2 font-serif-luxury text-3xl sm:text-4xl">Vinh danh đối tác dẫn đầu doanh thu</h2>
            </div>
            <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[#e8dcc7]">Cập nhật theo dữ liệu hệ thống</div>
        </div>

        <div class="space-y-3">
            @forelse($topPerformers as $index => $performer)
                @php
                    $medal = match($index) {
                        0 => 'Kim cương',
                        1 => 'Bạch kim',
                        2 => 'Vàng',
                        default => 'Tinh anh',
                    };
                    $ring = match($index) {
                        0 => 'border-[#d7b56d] bg-[#2b3c59]',
                        1 => 'border-[#bcc5d4] bg-[#273752]',
                        2 => 'border-[#c99a62] bg-[#26364f]',
                        default => 'border-[#2f4364] bg-[#1f2d45]',
                    };
                @endphp
                <article class="ranking-row {{ $ring }}">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="rank-index">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="avatar-chip">{{ strtoupper(substr($performer->name, 0, 1)) }}</div>
                        <div class="min-w-0">
                            <p class="truncate text-base font-black uppercase tracking-wide">{{ $performer->name }}</p>
                            <p class="mt-1 truncate text-[11px] uppercase tracking-[0.12em] text-[#d8dff0]">
                                ID {{ $performer->id }} - {{ $performer->phone ? substr($performer->phone, 0, 7) . '***' : 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 flex items-end justify-between gap-4 sm:mt-0 sm:block sm:text-right">
                        <span class="rounded-full border border-white/20 px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-[#f0dfb7]">{{ $medal }}</span>
                        <p class="mt-2 text-lg font-extrabold sm:text-xl">
                            {{ number_format($performer->total_revenue, 0, ',', '.') }}
                            <span class="text-[10px] font-bold uppercase text-[#d5dcf0]">VNĐ</span>
                        </p>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-white/20 bg-white/5 p-8 text-center text-sm text-[#dbe2f3]">
                    Chưa có dữ liệu vinh danh trong giai đoạn này.
                </div>
            @endforelse
        </div>
    </section>

    <section class="mt-10 rounded-[28px] border border-[#dcc9a3] bg-gradient-to-r from-[#fcf7ec] to-[#f7ecd7] p-6 sm:p-8 md:p-10 reveal-up"
        style="animation-delay:.32s;">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="max-w-2xl">
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#8a692f]">Sẵn sàng tăng tốc</p>
                <h2 class="mt-2 font-serif-luxury text-3xl leading-tight text-[#14233b] sm:text-4xl">
                    Trở thành CTV BĐS trong hệ thống
                    <span class="text-[#9e7830]">VM PHÚ THỊNH LAND</span>
                </h2>
                <p class="mt-3 text-sm text-[#4d5c76] sm:text-base">
                    Đăng ký tài khoản để nhận mã mời, bắt đầu tiếp cận nguồn hàng chất lượng và mở rộng doanh thu ngay từ tháng này.
                </p>
            </div>
            <a href="/login?register=1"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#9e7830] px-8 py-4 text-sm font-black uppercase tracking-[0.14em] text-white transition-all hover:-translate-y-0.5 hover:bg-[#85622a] hover:shadow-[0_16px_30px_-16px_rgba(158,120,48,0.65)]">
                Nhận mã mời ngay <i class="fa-solid fa-paper-plane text-xs"></i>
            </a>
        </div>
    </section>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap');

        .ctv-luxury-page {
            --ink: #14233b;
            --ink-soft: #4d5c76;
            --gold: #9e7830;
            --gold-soft: #d8c29a;
            --line: #e7dcc8;
            background: radial-gradient(circle at 8% 10%, #fff7e6 0%, #f9f2e7 38%, #f7f0e5 100%);
            color: var(--ink);
            font-family: 'Manrope', sans-serif;
        }

        .font-serif-luxury {
            font-family: 'Playfair Display', serif;
            letter-spacing: -0.02em;
        }

        .hero-shell {
            background: linear-gradient(125deg, rgba(255, 251, 243, 0.95), rgba(251, 243, 226, 0.9));
            box-shadow: 0 30px 70px -48px rgba(20, 35, 59, 0.55);
        }

        .hero-mesh {
            background-image:
                linear-gradient(to right, rgba(158, 120, 48, 0.08) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(158, 120, 48, 0.08) 1px, transparent 1px);
            background-size: 28px 28px;
            -webkit-mask-image: radial-gradient(circle at 20% 25%, #000 30%, transparent 78%);
            mask-image: radial-gradient(circle at 20% 25%, #000 30%, transparent 78%);
        }

        .stat-pill {
            border: 1px solid #e8dcc8;
            background: rgba(255, 255, 255, 0.72);
            border-radius: 16px;
            padding: 12px 14px;
        }

        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            font-weight: 800;
            color: #8e6d32;
        }

        .stat-value {
            margin-top: 6px;
            font-size: 24px;
            font-weight: 800;
            color: #14233b;
            line-height: 1;
        }

        .feature-line {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px dashed #e5d8bf;
            border-radius: 12px;
            padding: 8px 10px;
            background: #fffdf8;
        }

        .feature-line i {
            color: #9e7830;
            font-size: 12px;
        }

        .benefit-card {
            border: 1px solid #e7dcc8;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.78);
            padding: 22px;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }

        .benefit-card:hover {
            transform: translateY(-4px);
            border-color: #d2bd90;
            box-shadow: 0 20px 38px -28px rgba(20, 35, 59, 0.45);
        }

        .benefit-icon {
            height: 42px;
            width: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(130deg, #f3e4c8, #e7d0a2);
            color: #7b5c23;
            margin-bottom: 12px;
        }

        .benefit-title {
            font-size: 16px;
            font-weight: 800;
            color: #14233b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .benefit-copy {
            margin-top: 8px;
            font-size: 14px;
            line-height: 1.65;
            color: #4f5d77;
        }

        .step-box {
            border: 1px solid #e7dbc6;
            border-radius: 18px;
            background: #fffdfa;
            padding: 16px;
        }

        .step-no {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.2em;
            color: #8d6c30;
            margin-bottom: 8px;
        }

        .step-box h3 {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            color: #14233b;
        }

        .step-box p {
            margin-top: 6px;
            font-size: 14px;
            color: #516079;
            line-height: 1.6;
        }

        .ranking-row {
            border-width: 1px;
            border-radius: 20px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .ranking-row:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 34px -24px rgba(8, 16, 30, 0.6);
        }

        .rank-index {
            min-width: 38px;
            text-align: center;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.16em;
            color: #e6d4ad;
        }

        .avatar-chip {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(130deg, #d4b471, #9e7830);
            color: #101d31;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 900;
        }

        @media (min-width: 640px) {
            .ranking-row {
                flex-direction: row;
                align-items: center;
            }
        }

        .reveal-up {
            opacity: 0;
            transform: translateY(16px);
            animation: revealUp .7s ease forwards;
        }

        @keyframes revealUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</div>
