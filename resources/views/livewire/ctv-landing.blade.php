<div class="min-h-screen bg-[#050505] text-white selection:bg-blue-500/30 font-sans tracking-tight overflow-x-hidden">
    <!-- Navbar (Glassmorphism) -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-4 md:px-6 py-4 flex items-center justify-between backdrop-blur-md bg-black/20 border-b border-white/5">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center shadow-[0_0_20px_rgba(59,130,246,0.5)] transition-transform hover:scale-110">
                <i class="fa-solid fa-cloud-bolt text-white text-sm"></i>
            </div>
            <span class="text-lg md:text-xl font-black tracking-tighter uppercase italic">Antigravity</span>
        </div>
        <div class="flex items-center gap-4 md:gap-8">
            <a href="#ranking" class="hidden sm:inline-block text-[10px] md:text-sm font-bold uppercase tracking-widest text-gray-400 hover:text-white transition-colors">Vinh danh</a>
            <a href="/login" class="px-4 py-1.5 md:px-6 md:py-2 rounded-full border border-blue-500/50 text-blue-400 text-[10px] md:text-sm font-bold uppercase tracking-widest hover:bg-blue-500 hover:text-white transition-all shadow-[0_0_15px_rgba(59,130,246,0.2)]">Đăng nhập</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex flex-col items-center justify-center pt-20 px-6 overflow-hidden">
        <!-- Background Particles Emulation (blurred circles) -->
        <div class="absolute inset-0 z-0">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full blur-[120px] animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-indigo-600/10 rounded-full blur-[150px] animate-pulse" style="animation-delay: 2s"></div>
        </div>

        <div class="relative z-10 text-center max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-bold uppercase tracking-[0.2em] mb-8 animate-bounce">
                🚀 Kỷ nguyên Bất động sản mới
            </div>
            
            <h1 class="text-4xl sm:text-6xl md:text-8xl font-black leading-tight sm:leading-none mb-8 tracking-tighter bg-gradient-to-b from-white via-white to-white/40 bg-clip-text text-transparent">
                NÂNG TẦM <br class="hidden sm:block"/>
                <span class="bg-gradient-to-r from-blue-400 to-indigo-600 bg-clip-text text-transparent uppercase">Kinh doanh</span> <br/>
                KHÔNG TRỌNG LỰC
            </h1>

            <p class="text-lg md:text-xl text-gray-400 max-w-2xl mx-auto mb-12 font-medium leading-relaxed">
                Tham gia mạng lưới Cộng tác viên chuyên nghiệp nhất. Kiến tạo thu nhập đột phá với hệ thống quản trị thông minh từ Antigravity.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6">
                <a href="/login?register=1" class="w-full sm:w-auto group relative px-8 py-4 md:px-10 md:py-5 bg-blue-600 rounded-2xl text-white font-black uppercase tracking-widest overflow-hidden transition-all hover:shadow-[0_0_40px_rgba(37,99,235,0.6)]">
                    <span class="relative z-10 text-sm md:text-base">Bắt đầu ngay</span>
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 group-hover:scale-105 transition-transform"></div>
                </a>
                <a href="#ranking" class="w-full sm:w-auto px-8 py-4 md:px-10 md:py-5 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md text-white font-black text-sm md:text-base uppercase tracking-widest hover:bg-white/10 transition-all">
                    Xem xếp hạng
                </a>
            </div>
        </div>

        <!-- Floating Elements -->
        <div class="hidden sm:block absolute bottom-20 left-10 md:left-20 animate-float">
            <div class="backdrop-blur-xl bg-white/5 border border-white/10 p-4 rounded-2xl shadow-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center text-green-400">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Tăng trưởng</div>
                        <div class="text-xl font-black">+250%</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section (Luxury Cards) -->
    <section class="py-32 px-6 container mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="relative group p-10 rounded-[40px] bg-white/5 border border-white/10 backdrop-blur-3xl overflow-hidden">
                <div class="absolute top-0 right-0 p-8 text-6xl text-blue-500/10 font-black">01</div>
                <div class="text-gray-400 text-xs font-bold uppercase tracking-[0.3em] mb-4">Tổng doanh thu</div>
                <div class="text-5xl font-black mb-4 tracking-tighter">{{ number_format($stats['total_revenue'] / 1000000000, 1) }}B+</div>
                <div class="h-1 w-12 bg-blue-500 rounded-full mb-6"></div>
                <p class="text-sm text-gray-500 font-medium tracking-wide">Giá trị giao dịch đã được thực hiện thông qua hệ thống của chúng tôi.</p>
            </div>

            <div class="relative group p-10 rounded-[40px] bg-white/5 border border-white/10 backdrop-blur-3xl overflow-hidden scale-105 shadow-[0_30px_60px_-15px_rgba(59,130,246,0.3)]">
                <div class="absolute top-0 right-0 p-8 text-6xl text-indigo-500/10 font-black">02</div>
                <div class="text-blue-400 text-xs font-bold uppercase tracking-[0.3em] mb-4">Cộng tác viên</div>
                <div class="text-5xl font-black mb-4 tracking-tighter text-white">{{ number_format($stats['total_ctvs']) }}+</div>
                <div class="h-1 w-12 bg-indigo-500 rounded-full mb-6"></div>
                <p class="text-sm text-gray-400 font-medium tracking-wide">Mạng lưới CTV rộng khắp cả nước đang cùng nhau tạo nên giá trị.</p>
            </div>

            <div class="relative group p-10 rounded-[40px] bg-white/5 border border-white/10 backdrop-blur-3xl overflow-hidden">
                <div class="absolute top-0 right-0 p-8 text-6xl text-purple-500/10 font-black">03</div>
                <div class="text-gray-400 text-xs font-bold uppercase tracking-[0.3em] mb-4">Giao dịch thành công</div>
                <div class="text-5xl font-black mb-4 tracking-tighter">{{ number_format($stats['total_deals']) }}+</div>
                <div class="h-1 w-12 bg-purple-500 rounded-full mb-6"></div>
                <p class="text-sm text-gray-500 font-medium tracking-wide">Hàng ngàn giao dịch thành công mang lại lợi nhuận cho đối tác.</p>
            </div>
        </div>
    </section>

    <!-- Hall of Fame (Ranking) -->
    <section id="ranking" class="py-32 bg-gradient-to-b from-transparent to-white/[0.02]">
        <div class="container mx-auto px-6">
            <div class="text-center mb-20">
                <h2 class="text-xs font-black text-blue-500 uppercase tracking-[0.5em] mb-4">Hall of Fame</h2>
                <h3 class="text-4xl md:text-6xl font-black tracking-tighter">VINH DANH ĐỐI TÁC XUẤT SẮC</h3>
            </div>

            <div class="max-w-5xl mx-auto space-y-6">
                @foreach($topPerformers as $index => $performer)
                <div class="group relative flex flex-col sm:flex-row items-center sm:justify-between p-6 sm:p-8 rounded-[30px] sm:rounded-[38px] bg-white/[0.02] border border-white/5 transition-all hover:bg-white/5 hover:border-blue-500/30 hover:-translate-y-1 {{ $index == 0 ? 'bg-blue-500/[0.03] border-blue-500/20 shadow-[0_0_50px_rgba(59,130,246,0.1)]' : '' }}">
                    <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-8 w-full sm:w-auto text-center sm:text-left mb-4 sm:mb-0">
                        <div class="relative">
                            <div class="text-3xl sm:text-4xl font-black {{ $index == 0 ? 'text-yellow-400' : ($index == 1 ? 'text-gray-300' : ($index == 2 ? 'text-orange-400' : 'text-gray-700')) }} w-full sm:w-12 text-center">
                                #{{ $index + 1 }}
                            </div>
                            @if($index < 3)
                            <div class="absolute -top-4 -left-4 sm:-top-4 sm:-left-4 text-xs animate-bounce opacity-50">
                                <i class="fa-solid fa-crown text-yellow-500"></i>
                            </div>
                            @endif
                        </div>
                        <div class="w-16 h-16 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr {{ $index == 0 ? 'from-yellow-400 to-orange-500' : 'from-blue-500 to-indigo-600' }} flex items-center justify-center text-2xl font-black shadow-lg">
                            {{ substr($performer->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="flex flex-col sm:flex-row items-center gap-1 sm:gap-3">
                                <div class="text-lg sm:text-xl font-black uppercase tracking-tight">{{ $performer->name }}</div>
                                @if($performer->rank)
                                <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-tighter bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                    {{ $performer->rank->name }}
                                </span>
                                @endif
                            </div>
                            <div class="text-[10px] text-gray-500 font-mono tracking-widest mt-1 uppercase">ID: {{ $performer->id }} • {{ substr($performer->phone, 0, 7) }}***</div>
                        </div>
                    </div>
                    <div class="text-center sm:text-right border-t border-white/5 sm:border-0 pt-4 sm:pt-0 w-full sm:w-auto">
                        <div class="text-[10px] text-gray-400 uppercase font-black mb-1 tracking-widest">Doanh số</div>
                        <div class="text-xl sm:text-2xl font-mono text-blue-400 tracking-tighter">{{ number_format($performer->total_revenue, 0, ',', '.') }} <span class="text-[10px] text-gray-600 font-sans">VND</span></div>
                    </div>
                    
                    <!-- Glow effect on hover -->
                    <div class="absolute inset-0 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity bg-gradient-to-r from-blue-500/5 to-transparent pointer-events-none"></div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Footer / Registration CTA -->
    <footer class="py-40 text-center relative overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/10 rounded-full blur-[200px]"></div>
        
        <div class="relative z-10 max-w-3xl mx-auto px-6">
            <h2 class="text-3xl sm:text-5xl font-black mb-6 sm:mb-8 tracking-tighter italic uppercase leading-tight">SẴN SÀNG KHAI PHÁ <br class="hidden sm:block"/> TÌM NĂNG CÙNG ANTIGRAVITY?</h2>
            <p class="text-sm sm:text-base text-gray-400 mb-8 sm:mb-12 font-medium">Đăng ký tài khoản ngay hôm nay để nhận mã mời và bắt đầu xây dựng đế chế bất động sản của riêng bạn.</p>
            
            <a href="/register" class="w-full sm:w-auto inline-flex items-center justify-center gap-4 px-8 py-4 sm:px-12 sm:py-6 bg-white text-black rounded-2xl sm:rounded-3xl font-black uppercase tracking-widest hover:bg-blue-500 hover:text-white transition-all hover:shadow-[0_0_50px_rgba(59,130,246,0.3)]">
                Đăng ký ngay <i class="fa-solid fa-arrow-right"></i>
            </a>
            
            <div class="mt-20 border-t border-white/5 pt-10 flex flex-col md:flex-row items-center justify-between text-gray-600 text-[10px] font-black uppercase tracking-[0.3em]">
                <div>© 2026 Antigravity Real Estate Systems</div>
                <div class="flex gap-8 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</div>
