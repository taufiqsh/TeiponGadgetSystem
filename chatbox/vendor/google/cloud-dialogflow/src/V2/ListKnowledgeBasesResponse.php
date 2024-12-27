<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/knowledge_base.proto

namespace Google\Cloud\Dialogflow\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Response message for
 * [KnowledgeBases.ListKnowledgeBases][google.cloud.dialogflow.v2.KnowledgeBases.ListKnowledgeBases].
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.ListKnowledgeBasesResponse</code>
 */
class ListKnowledgeBasesResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * The list of knowledge bases.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dialogflow.v2.KnowledgeBase knowledge_bases = 1;</code>
     */
    private $knowledge_bases;
    /**
     * Token to retrieve the next page of results, or empty if there are no
     * more results in the list.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     */
    private $next_page_token = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array<\Google\Cloud\Dialogflow\V2\KnowledgeBase>|\Google\Protobuf\Internal\RepeatedField $knowledge_bases
     *           The list of knowledge bases.
     *     @type string $next_page_token
     *           Token to retrieve the next page of results, or empty if there are no
     *           more results in the list.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\KnowledgeBase::initOnce();
        parent::__construct($data);
    }

    /**
     * The list of knowledge bases.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dialogflow.v2.KnowledgeBase knowledge_bases = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getKnowledgeBases()
    {
        return $this->knowledge_bases;
    }

    /**
     * The list of knowledge bases.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dialogflow.v2.KnowledgeBase knowledge_bases = 1;</code>
     * @param array<\Google\Cloud\Dialogflow\V2\KnowledgeBase>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setKnowledgeBases($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Dialogflow\V2\KnowledgeBase::class);
        $this->knowledge_bases = $arr;

        return $this;
    }

    /**
     * Token to retrieve the next page of results, or empty if there are no
     * more results in the list.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @return string
     */
    public function getNextPageToken()
    {
        return $this->next_page_token;
    }

    /**
     * Token to retrieve the next page of results, or empty if there are no
     * more results in the list.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setNextPageToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->next_page_token = $var;

        return $this;
    }

}

