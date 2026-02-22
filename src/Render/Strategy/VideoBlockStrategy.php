<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

final readonly class VideoBlockStrategy extends AbstractBlockStrategy
{
    public function supports(array $block): bool
    {
        return ($block['type'] ?? '') === 'video';
    }

    public function prepareData(array $data): array
    {
        $url = $data['url'] ?? '';
        if (!$url) {
            return $data;
        }

        $embedUrl = null;
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            // Extract YouTube ID
            // Format: https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID
            $videoId = null;
            if (str_contains($url, 'v=')) {
                $parts = explode('v=', $url);
                $videoId = explode('&', end($parts))[0];
            } elseif (str_contains($url, 'youtu.be/')) {
                $parts = explode('youtu.be/', $url);
                $videoId = explode('?', end($parts))[0];
            }

            if ($videoId) {
                $embedUrl = 'https://www.youtube.com/embed/'.$videoId;
                $data['provider'] = 'youtube';
            }
        } elseif (str_contains($url, 'vimeo.com')) {
            // Extract Vimeo ID
            // Format: https://vimeo.com/VIDEO_ID
            $parts = explode('/', parse_url($url, \PHP_URL_PATH));
            $videoId = end($parts);

            if ($videoId && is_numeric($videoId)) {
                $embedUrl = 'https://player.vimeo.com/video/'.$videoId;
                $data['provider'] = 'vimeo';
            }
        }

        if ($embedUrl) {
            $data['embedUrl'] = $embedUrl;
        }

        return $data;
    }
}
