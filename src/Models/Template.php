<?php

namespace Notigen\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'notigen_templates';
    
    protected $fillable = [
        'name',
        'identifier',
        'description',
        'channels',
        'variables',
        'channel_contents',
        'is_active'
    ];

    protected $casts = [
        'channels' => 'array',
        'variables' => 'array',
        'channel_contents' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get content for a specific channel
     *
     * @param string $channel
     * @return array|null
     */
    public function getChannelContent(string $channel): ?array
    {
        return $this->channel_contents[$channel] ?? null;
    }

    /**
     * Set content for a specific channel
     *
     * @param string $channel
     * @param array $content
     * @return void
     */
    public function setChannelContent(string $channel, array $content): void
    {
        $contents = $this->channel_contents ?? [];
        $contents[$channel] = $content;
        $this->channel_contents = $contents;
    }
}
