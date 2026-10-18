<?php

/*
 * Author: Andi Ferdiawan <dev@aw-advantage.com>
 * Copyright (c) 2026, Andi Ferdiawan
 */

namespace App\Actions\Chat\Whatsapp;

use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Meta cannot read a fragmented MP4 — the kind screen recorders and streaming muxers
 * produce, where `moov` holds only track declarations and the samples live in repeating
 * `moof`/`mdat` fragments. It accepts the upload, then fails the send by webhook with
 * "No video stream found in given video file", which points nowhere near the cause.
 *
 * Rewriting the container fixes it. This is a remux, not a re-encode: the streams are
 * copied untouched, so there is no quality loss and it costs about a second.
 */
class NormaliseWhatsappVideo
{
    use AsAction;

    protected const TIMEOUT_SECONDS = 120;

    /**
     * Octane gives the whole request 45 seconds, and the upload to Meta comes after this.
     */
    protected const TRANSCODE_TIMEOUT_SECONDS = 35;

    /**
     * Returns a path to a progressive MP4, which is the original when it already is one.
     * A failure here is never fatal: the original is used and Meta decides.
     */
    public function handle(string $path): string
    {
        if (!$this->isFragmented($path)) {
            return $path;
        }

        $target = $this->temporaryMp4Path();

        $process = new Process([
            config('services.ffmpeg.path', 'ffmpeg'),
            '-y',
            '-i', $path,
            '-c', 'copy',
            '-movflags', '+faststart',
            $target,
        ]);

        $process->setTimeout(self::TIMEOUT_SECONDS);

        try {
            $process->mustRun();
        } catch (ProcessFailedException|RuntimeException $e) {
            @unlink($target);

            Log::warning('Fragmented WhatsApp video could not be remuxed', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);

            return $path;
        }

        if (!is_file($target) || filesize($target) === 0) {
            @unlink($target);

            return $path;
        }

        return $target;
    }

    /**
     * A phone's video is usually a MOV in HEVC, which Meta refuses outright: it plays only
     * H.264 with AAC in an MP4, at up to 16MB. Re-encoding at no more than 720p brings a
     * typical product clip of a minute well under that, in a few seconds.
     *
     * Returns the path to the converted MP4, or null when ffmpeg could not read the file.
     */
    public function transcode(string $path): ?string
    {
        $target = $this->temporaryMp4Path();

        $process = new Process([
            config('services.ffmpeg.path', 'ffmpeg'),
            '-y',
            '-i', $path,
            '-map', '0:v:0',
            '-map', '0:a:0?',
            '-vf', "scale='min(1280,iw)':'min(1280,ih)':force_original_aspect_ratio=decrease:force_divisible_by=2",
            '-c:v', 'libx264',
            '-preset', 'veryfast',
            '-crf', '28',
            '-maxrate', '1500k',
            '-bufsize', '3000k',
            '-pix_fmt', 'yuv420p',
            '-c:a', 'aac',
            '-b:a', '96k',
            '-ac', '2',
            '-movflags', '+faststart',
            $target,
        ]);

        $process->setTimeout(self::TRANSCODE_TIMEOUT_SECONDS);

        try {
            $process->mustRun();
        } catch (ProcessFailedException|RuntimeException $e) {
            @unlink($target);

            Log::warning('Video could not be converted for WhatsApp', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (!is_file($target) || filesize($target) === 0) {
            @unlink($target);

            return null;
        }

        return $target;
    }

    /**
     * tempnam() creates the file it names, so the .mp4 beside it would leave that one behind.
     */
    protected function temporaryMp4Path(): string
    {
        $placeholder = tempnam(sys_get_temp_dir(), 'wa-video-');
        @unlink($placeholder);

        return $placeholder.'.mp4';
    }

    /**
     * A `moof` box among the top-level boxes is what makes an MP4 fragmented. Reading the
     * box headers is enough — there is no need to decode anything.
     */
    public function isFragmented(string $path): bool
    {
        $handle = @fopen($path, 'rb');

        if (!$handle) {
            return false;
        }

        try {
            $size = filesize($path) ?: 0;
            $offset = 0;

            // A progressive file reaches its media data within a handful of boxes; the cap
            // stops a malformed file from being walked indefinitely.
            for ($i = 0; $i < 20 && $offset < $size; $i++) {
                if (fseek($handle, $offset) !== 0) {
                    return false;
                }

                $header = fread($handle, 8);

                if ($header === false || strlen($header) < 8) {
                    return false;
                }

                $boxSize = unpack('N', substr($header, 0, 4))[1];
                $boxType = substr($header, 4, 4);

                if ($boxType === 'moof') {
                    return true;
                }

                if ($boxSize === 1) {
                    $largeSize = fread($handle, 8);

                    if ($largeSize === false || strlen($largeSize) < 8) {
                        return false;
                    }

                    $boxSize = unpack('J', $largeSize)[1];
                }

                if ($boxSize < 8) {
                    return false;
                }

                $offset += $boxSize;
            }
        } finally {
            fclose($handle);
        }

        return false;
    }
}
