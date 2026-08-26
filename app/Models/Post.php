<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string $excerpt
 * @property string $category
 */
#[Fillable(['title', 'excerpt', 'category'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * @param Builder<Post> $query
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
     * @param Builder<Post> $query
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
}
