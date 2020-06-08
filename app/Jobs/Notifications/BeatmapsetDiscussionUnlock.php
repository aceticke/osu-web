<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Jobs\Notifications;

use App\Models\Notification;

class BeatmapsetDiscussionUnlock extends BeatmapsetNotification
{
    public static function getMailText(Notification $notification): string
    {
        $link = static::getMailLink($notification);

        return "Beatmapset has been unlocked {$link}";
    }
}
