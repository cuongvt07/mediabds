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

        if ($request->filled('tag')) {
            $tag = (string) $request->string('tag');
            $query->where(function ($q) use ($tag) {
                $q->where('category_tag', $tag)
                    ->orWhereJsonContains('tags', $tag);
            });
        }

        $page = $query->orderByDesc('published_at')->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => collect($page->items())->map(function (BlogPost $post) {
                return $this->mapPost($post);
            })->values(),
            'meta' => [
                'page' => $page->currentPage(),
                'pageSize' => $page->perPage(),
                'total' => $page->total(),
                'totalPages' => $page->lastPage(),
            ],
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
            'id' => (string) $post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt ?? '',
            'content' => $post->content,
            'coverImage' => $post->cover_image ?? '',
            'authorName' => $post->author_name ?? 'BDS Việt',
            'categoryTag' => $post->category_tag ?? 'Tin tức',
            'tags' => $post->tags ?? [],
            'readingMinutes' => (int) $post->reading_minutes,
            'publishedAt' => optional($post->published_at ?? $post->created_at)->toISOString(),
            'updatedAt' => optional($post->updated_at)->toISOString(),
        ];
    }
}
