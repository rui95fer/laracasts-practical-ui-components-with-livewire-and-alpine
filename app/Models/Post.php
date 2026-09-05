<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string $excerpt
 * @property string|null $content
 * @property string $category
 * @property bool $published
 * @property int|null $author_id
 */
#[Fillable(['title', 'excerpt', 'content', 'category', 'published', 'author_id'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @param  Builder<Post>  $query
     */
    #[Scope]
    protected function category(Builder $query, string $category): void
    {
        if ($category === '') {
            return;
        }

        $query->where('category', $category);
    }

    /**
     * @param  Builder<Post>  $query
     */
    #[Scope]
    protected function search(Builder $query, string $search): void
    {
        $search = Str::of($search)->trim()->lower()->toString();

        if ($search === '') {
            return;
        }

        $search = "%{$search}%";

        $query->where(function (Builder $query) use ($search): void {
            $query
                ->whereRaw('LOWER(title) LIKE ?', [$search])
                ->orWhereRaw('LOWER(excerpt) LIKE ?', [$search]);
        });
    }

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
        ];
    }
}
