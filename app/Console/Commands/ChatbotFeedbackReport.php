<?php

namespace App\Console\Commands;

use App\Models\ChatFeedback;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ChatbotFeedbackReport extends Command
{
    protected $signature = 'chatbot:feedback-report
                            {--days=7 : Số ngày gần nhất}
                            {--top=10 : Số message lỗi hiển thị}';

    protected $description = 'Thống kê feedback chatbot: tỷ lệ 👍/👎, nhóm lỗi phổ biến, các message bị vote sai';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $top = (int) $this->option('top');
        $since = now()->subDays($days);

        $total = ChatFeedback::where('created_at', '>=', $since)->count();
        if ($total === 0) {
            $this->info("Chưa có feedback nào trong {$days} ngày qua.");
            return self::SUCCESS;
        }

        $up = ChatFeedback::where('created_at', '>=', $since)->where('rating', 1)->count();
        $down = ChatFeedback::where('created_at', '>=', $since)->where('rating', -1)->count();
        $rate = $total > 0 ? round($up / $total * 100, 1) : 0;

        $this->info("=== Feedback chatbot {$days} ngày qua ===");
        $this->line("Tổng vote: {$total} (👍 {$up} · 👎 {$down}) · Tỷ lệ hài lòng: {$rate}%");
        $this->newLine();

        // Nhóm lỗi
        $this->info('--- Nhóm lỗi phổ biến (chỉ tính 👎) ---');
        $byCategory = ChatFeedback::where('created_at', '>=', $since)
            ->where('rating', -1)
            ->whereNotNull('error_category')
            ->groupBy('error_category')
            ->select('error_category', DB::raw('COUNT(*) as cnt'))
            ->orderByDesc('cnt')
            ->get();

        $cats = ChatFeedback::ERROR_CATEGORIES;
        $rows = [];
        foreach ($byCategory as $c) {
            $rows[] = [$cats[$c->error_category] ?? $c->error_category, $c->cnt];
        }
        if (!empty($rows)) $this->table(['Loại lỗi', 'Số lượt'], $rows);
        $this->newLine();

        // Intent bị fail
        $this->info('--- Intent có nhiều 👎 nhất ---');
        $byIntent = ChatFeedback::where('created_at', '>=', $since)
            ->where('rating', -1)
            ->whereNotNull('intent_at_time')
            ->groupBy('intent_at_time')
            ->select('intent_at_time', DB::raw('COUNT(*) as cnt'), DB::raw('AVG(confidence_at_time) as avg_conf'))
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();
        $rows = [];
        foreach ($byIntent as $i) {
            $rows[] = [$i->intent_at_time, $i->cnt, $i->avg_conf ? round($i->avg_conf, 2) : '-'];
        }
        if (!empty($rows)) $this->table(['Intent', '👎', 'Avg confidence'], $rows);
        $this->newLine();

        // Top message lỗi để fix
        $this->info("--- {$top} message bị vote 👎 gần nhất (để review) ---");
        $bad = ChatFeedback::where('created_at', '>=', $since)
            ->where('rating', -1)
            ->with(['chatMessage:id,content,role'])
            ->orderByDesc('created_at')
            ->limit($top)
            ->get();
        foreach ($bad as $b) {
            $this->line("--- ID {$b->chat_message_id} · " . ($cats[$b->error_category] ?? '?') . " ---");
            $this->line('Bot trả lời: ' . mb_substr($b->chatMessage?->content ?? '', 0, 200));
            if ($b->note) $this->line("Ghi chú user: {$b->note}");
            if (!empty($b->tool_calls_meta)) {
                $toolNames = array_map(fn($t) => $t['name'] ?? '?', $b->tool_calls_meta);
                $this->line('Tools đã dùng: ' . implode(', ', $toolNames));
            }
            $this->newLine();
        }

        return self::SUCCESS;
    }
}
