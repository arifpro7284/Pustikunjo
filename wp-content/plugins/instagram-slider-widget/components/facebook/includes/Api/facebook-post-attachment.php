<?php

namespace WIS\Facebook\Includes\Api;

/**
 * Class WFB_Post_Attachment
 *
 * @property string $media_type
 * @property \stdClass $media
 * @property string $type
 * @property string $url
 *
 * @package WIS\Facebook\Includes\Api
 */
class WFB_Post_Attachment{
    public $media_type;
    public $media;
	public $type;
	public $url;

    /**
     * @param $attachments
     *
     * @return $this
     */
    public function get_attachment_from_object($attachments)
    {
        $this->media_type = isset( $attachments->media_type ) ? $attachments->media_type : '';
        $this->media = isset( $attachments->media ) ? $attachments->media : '';
        $this->type = isset( $attachments->type ) ? $attachments->type : '';
        $this->url = isset( $attachments->url ) ? $attachments->url : '';

        return $this;
    }
}
