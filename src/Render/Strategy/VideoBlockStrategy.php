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
        $url = isset($data['url']) && \is_string($data['url']) ? $data['url'] : '';
        if ('' === $url) {
            return $data;
        }

        $embedUrl = null;
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            // Extract YouTube ID
            // Format: https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID
            $videoId = null;
            if (str_contains($url, 'v=')) {
                $parts = explode('v=', $url);
                $last = end($parts);
                $videoId = \is_string($last) ? explode('&', $last)[0] : null;
            } elseif (str_contains($url, 'youtu.be/')) {
                $parts = explode('youtu.be/', $url);
                $last = end($parts);
                $videoId = \is_string($last) ? explode('?', $last)[0] : null;
            }

            if (null !== $videoId && '' !== $videoId) {
                $embedUrl = 'https://www.youtube.com/embed/'.$videoId;
                $data['provider'] = 'youtube';
            }
        } elseif (str_contains($url, 'vimeo.com')) {
            // Extract Vimeo ID
            // Format: https://vimeo.com/VIDEO_ID
            $path = parse_url($url, \PHP_URL_PATH);
            $pathStr = \is_string($path) ? $path : '';
            $parts = explode('/', $pathStr);
            $videoId = end($parts);

            if (\is_string($videoId) && '' !== $videoId && is_numeric($videoId)) {
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
