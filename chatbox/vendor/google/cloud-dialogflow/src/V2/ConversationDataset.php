<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/conversation_dataset.proto

namespace Google\Cloud\Dialogflow\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Represents a conversation dataset that a user imports raw data into.
 * The data inside ConversationDataset can not be changed after
 * ImportConversationData finishes (and calling ImportConversationData on a
 * dataset that already has data is not allowed).
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.ConversationDataset</code>
 */
class ConversationDataset extends \Google\Protobuf\Internal\Message
{
    /**
     * Output only. ConversationDataset resource name. Format:
     * `projects/<Project ID>/locations/<Location
     * ID>/conversationDatasets/<Conversation Dataset ID>`
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $name = '';
    /**
     * Required. The display name of the dataset. Maximum of 64 bytes.
     *
     * Generated from protobuf field <code>string display_name = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $display_name = '';
    /**
     * Optional. The description of the dataset. Maximum of 10000 bytes.
     *
     * Generated from protobuf field <code>string description = 3 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $description = '';
    /**
     * Output only. Creation time of this dataset.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $create_time = null;
    /**
     * Output only. Input configurations set during conversation data import.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.InputConfig input_config = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $input_config = null;
    /**
     * Output only. Metadata set during conversation data import.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.ConversationInfo conversation_info = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $conversation_info = null;
    /**
     * Output only. The number of conversations this conversation dataset
     * contains.
     *
     * Generated from protobuf field <code>int64 conversation_count = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $conversation_count = 0;
    /**
     * Output only. A read only boolean field reflecting Zone Isolation status of
     * the dataset.
     *
     * Generated from protobuf field <code>optional bool satisfies_pzi = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $satisfies_pzi = null;
    /**
     * Output only. A read only boolean field reflecting Zone Separation status of
     * the dataset.
     *
     * Generated from protobuf field <code>optional bool satisfies_pzs = 9 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $satisfies_pzs = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Output only. ConversationDataset resource name. Format:
     *           `projects/<Project ID>/locations/<Location
     *           ID>/conversationDatasets/<Conversation Dataset ID>`
     *     @type string $display_name
     *           Required. The display name of the dataset. Maximum of 64 bytes.
     *     @type string $description
     *           Optional. The description of the dataset. Maximum of 10000 bytes.
     *     @type \Google\Protobuf\Timestamp $create_time
     *           Output only. Creation time of this dataset.
     *     @type \Google\Cloud\Dialogflow\V2\InputConfig $input_config
     *           Output only. Input configurations set during conversation data import.
     *     @type \Google\Cloud\Dialogflow\V2\ConversationInfo $conversation_info
     *           Output only. Metadata set during conversation data import.
     *     @type int|string $conversation_count
     *           Output only. The number of conversations this conversation dataset
     *           contains.
     *     @type bool $satisfies_pzi
     *           Output only. A read only boolean field reflecting Zone Isolation status of
     *           the dataset.
     *     @type bool $satisfies_pzs
     *           Output only. A read only boolean field reflecting Zone Separation status of
     *           the dataset.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\ConversationDataset::initOnce();
        parent::__construct($data);
    }

    /**
     * Output only. ConversationDataset resource name. Format:
     * `projects/<Project ID>/locations/<Location
     * ID>/conversationDatasets/<Conversation Dataset ID>`
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Output only. ConversationDataset resource name. Format:
     * `projects/<Project ID>/locations/<Location
     * ID>/conversationDatasets/<Conversation Dataset ID>`
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

    /**
     * Required. The display name of the dataset. Maximum of 64 bytes.
     *
     * Generated from protobuf field <code>string display_name = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * Required. The display name of the dataset. Maximum of 64 bytes.
     *
     * Generated from protobuf field <code>string display_name = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setDisplayName($var)
    {
        GPBUtil::checkString($var, True);
        $this->display_name = $var;

        return $this;
    }

    /**
     * Optional. The description of the dataset. Maximum of 10000 bytes.
     *
     * Generated from protobuf field <code>string description = 3 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Optional. The description of the dataset. Maximum of 10000 bytes.
     *
     * Generated from protobuf field <code>string description = 3 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param string $var
     * @return $this
     */
    public function setDescription($var)
    {
        GPBUtil::checkString($var, True);
        $this->description = $var;

        return $this;
    }

    /**
     * Output only. Creation time of this dataset.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    public function hasCreateTime()
    {
        return isset($this->create_time);
    }

    public function clearCreateTime()
    {
        unset($this->create_time);
    }

    /**
     * Output only. Creation time of this dataset.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setCreateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->create_time = $var;

        return $this;
    }

    /**
     * Output only. Input configurations set during conversation data import.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.InputConfig input_config = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Cloud\Dialogflow\V2\InputConfig|null
     */
    public function getInputConfig()
    {
        return $this->input_config;
    }

    public function hasInputConfig()
    {
        return isset($this->input_config);
    }

    public function clearInputConfig()
    {
        unset($this->input_config);
    }

    /**
     * Output only. Input configurations set during conversation data import.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.InputConfig input_config = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Cloud\Dialogflow\V2\InputConfig $var
     * @return $this
     */
    public function setInputConfig($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Dialogflow\V2\InputConfig::class);
        $this->input_config = $var;

        return $this;
    }

    /**
     * Output only. Metadata set during conversation data import.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.ConversationInfo conversation_info = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Cloud\Dialogflow\V2\ConversationInfo|null
     */
    public function getConversationInfo()
    {
        return $this->conversation_info;
    }

    public function hasConversationInfo()
    {
        return isset($this->conversation_info);
    }

    public function clearConversationInfo()
    {
        unset($this->conversation_info);
    }

    /**
     * Output only. Metadata set during conversation data import.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.ConversationInfo conversation_info = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Cloud\Dialogflow\V2\ConversationInfo $var
     * @return $this
     */
    public function setConversationInfo($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Dialogflow\V2\ConversationInfo::class);
        $this->conversation_info = $var;

        return $this;
    }

    /**
     * Output only. The number of conversations this conversation dataset
     * contains.
     *
     * Generated from protobuf field <code>int64 conversation_count = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return int|string
     */
    public function getConversationCount()
    {
        return $this->conversation_count;
    }

    /**
     * Output only. The number of conversations this conversation dataset
     * contains.
     *
     * Generated from protobuf field <code>int64 conversation_count = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param int|string $var
     * @return $this
     */
    public function setConversationCount($var)
    {
        GPBUtil::checkInt64($var);
        $this->conversation_count = $var;

        return $this;
    }

    /**
     * Output only. A read only boolean field reflecting Zone Isolation status of
     * the dataset.
     *
     * Generated from protobuf field <code>optional bool satisfies_pzi = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return bool
     */
    public function getSatisfiesPzi()
    {
        return isset($this->satisfies_pzi) ? $this->satisfies_pzi : false;
    }

    public function hasSatisfiesPzi()
    {
        return isset($this->satisfies_pzi);
    }

    public function clearSatisfiesPzi()
    {
        unset($this->satisfies_pzi);
    }

    /**
     * Output only. A read only boolean field reflecting Zone Isolation status of
     * the dataset.
     *
     * Generated from protobuf field <code>optional bool satisfies_pzi = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param bool $var
     * @return $this
     */
    public function setSatisfiesPzi($var)
    {
        GPBUtil::checkBool($var);
        $this->satisfies_pzi = $var;

        return $this;
    }

    /**
     * Output only. A read only boolean field reflecting Zone Separation status of
     * the dataset.
     *
     * Generated from protobuf field <code>optional bool satisfies_pzs = 9 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return bool
     */
    public function getSatisfiesPzs()
    {
        return isset($this->satisfies_pzs) ? $this->satisfies_pzs : false;
    }

    public function hasSatisfiesPzs()
    {
        return isset($this->satisfies_pzs);
    }

    public function clearSatisfiesPzs()
    {
        unset($this->satisfies_pzs);
    }

    /**
     * Output only. A read only boolean field reflecting Zone Separation status of
     * the dataset.
     *
     * Generated from protobuf field <code>optional bool satisfies_pzs = 9 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param bool $var
     * @return $this
     */
    public function setSatisfiesPzs($var)
    {
        GPBUtil::checkBool($var);
        $this->satisfies_pzs = $var;

        return $this;
    }

}
