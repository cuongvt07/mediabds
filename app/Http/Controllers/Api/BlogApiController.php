<?php

namespace App\Http\Controllers\Api;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('pageSize', $request->integer('per_page', 10)), 1), 30);

        $query = BlogPost::query()
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });

        // Filter theo loại: bds | xe
        if ($request->filled('type') && in_array($request->string('type'), ['bds', 'xe', 'general'])) {
            $query->where('type', $request->string('type'));
        }

        // Filter theo tag (category_tag hoặc tags JSON)
        if ($request->filled('tag')) {
            $tag = (string) $request->string('tag');
            $query->where(function ($q) use ($tag) {
                $q->where('category_tag', $tag)
                    ->orWhereJsonContains('tags', $tag);
            });
        }

        $page = $query->orderByDesc('published_at')->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => collect($page->items())->map(fn (BlogPost $post) => $this->mapPost($post))->values(),
            'meta' => [
                'page'       => $page->currentPage(),
                'pageSize'   => $page->perPage(),
                'total'      => $page->total(),
                'totalPages' => $page->lastPage(),
            ],
        ]);
    }

    /**
     * Trả về BĐS + Xe cùng lúc cho homepage split-block.
     * GET /api/v1/blogs/split?limit=4
     */
    public function split(Request $request)
    {
        $limit = min(max((int) $request->integer('limit', 4), 1), 12);

        $base = fn () => BlogPost::query()
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        $bds = (clone $base())->where('type', 'bds')->limit($limit)->get();
        $xe  = (clone $base())->where('type', 'xe')->limit($limit)->get();

        return response()->json([
            'bds' => collect($bds)->map(fn (BlogPost $post) => $this->mapPost($post))->values(),
            'xe'  => collect($xe)->map(fn (BlogPost $post) => $this->mapPost($post))->values(),
        ]);
    }

    public function show(string $slug)
    {
        $post = BlogPost::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->first();

        if (! $post) {
            return $this->fail('Không tìm thấy bài viết', 404);
        }

        return $this->ok($this->mapPost($post));
    }

    private function mapPost(BlogPost $post): array
    {
        return [
            'id'             => (string) $post->id,
            'slug'           => $post->slug,
            'title'          => $post->title,
            'excerpt'        => $post->excerpt ?? '',
            'content'        => $post->content,
            'coverImage'     => $post->cover_image ?? '',
            'authorName'     => $post->author_name ?? 'BDS Việt',
            'categoryTag'    => $post->category_tag ?? 'Tin tức',
            'type'           => $post->type ?? 'bds',  // 'bds' | 'xe' | 'general'
            'tags'           => $post->tags ?? [],
            'readingMinutes' => (int) $post->reading_minutes,
            'publishedAt'    => optional($post->published_at ?? $post->created_at)->toISOString(),
            'updatedAt'      => optional($post->updated_at)->toISOString(),
        ];
    }
}
