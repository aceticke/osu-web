<?php

/**
 *    Copyright 2015 ppy Pty. Ltd.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace App\Models\Forum;

use App\Libraries\ImageProcessor;
use App\Libraries\StorageAuto;
use App\Models\User;
use DB;
use Illuminate\Database\Eloquent\Model;

class TopicCover extends Model
{
    protected $table = 'forum_topic_covers';

    private $storage = null;

    public static function upload($filePath)
    {
        $cover = new static;

        DB::transaction(function () use ($cover, $filePath) {
            $cover->save(); // get id
            $cover->storeFile($filePath);
            $cover->save();
        });

        return $cover;
    }

    public function __construct($attributes = [])
    {
        $this->storage = StorageAuto::get();

        return parent::__construct($attributes);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fileDir()
    {
        return "topic-covers/{$this->id}";
    }

    public function fileName()
    {
        return "{$this->hash}.{$this->ext}";
    }

    public function filePath()
    {
        return $this->fileDir().'/'.$this->fileName();
    }

    public function deleteWithFile()
    {
        $this->deleteFile();

        return $this->delete();
    }

    public function deleteFile()
    {
        if (presence($this->hash) === null) {
            return;
        }

        return $this->storage->deleteDirectory($this->fileDir());
    }

    public function storeFile($filePath)
    {
        $image = new ImageProcessor($filePath, [2700, 700], 1000000);
        $image->process();

        $this->deleteFile();
        $this->hash = hash_file('sha256', $image->inputPath);
        $this->ext = $image->ext();

        $this->storage->put($this->filePath(), file_get_contents($image->inputPath));
    }
}
