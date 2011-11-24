<?php
/*
 * Copyright (C) 2011 Josef Kufner  <jk@frozen-doe.net>
 *
 * This file is part of glip.
 *
 * glip is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.

 * glip is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with glip.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('git_object.class.php');
require_once('git_commit_stamp.class.php');

class GitTag extends GitObject
{
    /**
     * @brief (string) Tag name
     */
    public $tag;

    /**
     * @brief (array of GitObject) Targets the tag is pointing to.
     * strings.
     */
    public $objects;

    /**
     * @brief (GitCommitStamp) The committer of this commit.
     */
    public $tagger;

    /**
     * @brief (string) Commit summary, i.e. the first line of the commit message.
     */
    public $summary;

    /**
     * @brief (string) Everything after the first line of the commit message.
     */
    public $detail;

    public function __construct($repo)
    {
        parent::__construct($repo, Git::OBJ_TAG);
    }

    public function _unserialize($data)
    {
        $lines = explode("\n", $data);
        unset($data);
        $meta = array('object' => array());
        while (($line = array_shift($lines)) != '')
        {
            $parts = explode(' ', $line, 2);
            if (!isset($meta[$parts[0]]))
                $meta[$parts[0]] = array($parts[1]);
            else
                $meta[$parts[0]][] = $parts[1];
        }

        $this->tag = $meta['tag'][0];
        $this->objects = array_map('sha1_bin', $meta['object']);
        $this->tagger = new GitCommitStamp;
        $this->tagger->unserialize($meta['tagger'][0]);

        $this->summary = array_shift($lines);
        $this->detail = implode("\n", $lines);

        $this->history = NULL;
    }

    public function _serialize()
    {
        $s = '';
        foreach ($this->objects as $object)
            $s .= sprintf("object %s\n", sha1_hex($object));
        $s .= sprintf("tag %s\n", $this->tag);
        $s .= sprintf("tagger %s\n", $this->tagger->serialize());
        $s .= "\n".$this->summary."\n".$this->detail;
        return $s;
    }

    /**
     * @brief Get commit the tag is pointing to.
     *
     * @returns (array of GitCommit)
     */
    public function getObjects()
    {
        return $this->objects;
    }

}

