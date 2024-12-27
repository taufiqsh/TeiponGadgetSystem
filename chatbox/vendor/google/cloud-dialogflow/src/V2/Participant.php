<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/participant.proto

namespace Google\Cloud\Dialogflow\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Represents a conversation participant (human agent, virtual agent, end-user).
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.Participant</code>
 */
class Participant extends \Google\Protobuf\Internal\Message
{
    /**
     * Optional. The unique identifier of this participant.
     * Format: `projects/<Project ID>/locations/<Location
     * ID>/conversations/<Conversation ID>/participants/<Participant ID>`.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $name = '';
    /**
     * Immutable. The role this participant plays in the conversation. This field
     * must be set during participant creation and is then immutable.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.Participant.Role role = 2 [(.google.api.field_behavior) = IMMUTABLE];</code>
     */
    private $role = 0;
    /**
     * Optional. Label applied to streams representing this participant in SIPREC
     * XML metadata and SDP. This is used to assign transcriptions from that
     * media stream to this participant. This field can be updated.
     *
     * Generated from protobuf field <code>string sip_recording_media_label = 6 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $sip_recording_media_label = '';
    /**
     * Optional. Obfuscated user id that should be associated with the created
     * participant.
     * You can specify a user id as follows:
     * 1. If you set this field in
     *    [CreateParticipantRequest][google.cloud.dialogflow.v2.CreateParticipantRequest.participant]
     *    or
     *    [UpdateParticipantRequest][google.cloud.dialogflow.v2.UpdateParticipantRequest.participant],
     *    Dialogflow adds the obfuscated user id with the participant.
     * 2. If you set this field in
     *    [AnalyzeContent][google.cloud.dialogflow.v2.AnalyzeContentRequest.participant]
     *    or
     *    [StreamingAnalyzeContent][google.cloud.dialogflow.v2.StreamingAnalyzeContentRequest.participant],
     *    Dialogflow will update
     *    [Participant.obfuscated_external_user_id][google.cloud.dialogflow.v2.Participant.obfuscated_external_user_id].
     * Dialogflow returns an error if you try to add a user id for a
     * non-[END_USER][google.cloud.dialogflow.v2.Participant.Role.END_USER]
     * participant.
     * Dialogflow uses this user id for billing and measurement purposes. For
     * example, Dialogflow determines whether a user in one conversation returned
     * in a later conversation.
     * Note:
     * * Please never pass raw user ids to Dialogflow. Always obfuscate your user
     *   id first.
     * * Dialogflow only accepts a UTF-8 encoded string, e.g., a hex digest of a
     *   hash function like SHA-512.
     * * The length of the user id must be <= 256 characters.
     *
     * Generated from protobuf field <code>string obfuscated_external_user_id = 7 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $obfuscated_external_user_id = '';
    /**
     * Optional. Key-value filters on the metadata of documents returned by
     * article suggestion. If specified, article suggestion only returns suggested
     * documents that match all filters in their
     * [Document.metadata][google.cloud.dialogflow.v2.Document.metadata]. Multiple
     * values for a metadata key should be concatenated by comma. For example,
     * filters to match all documents that have 'US' or 'CA' in their market
     * metadata values and 'agent' in their user metadata values will be
     * ```
     * documents_metadata_filters {
     *   key: "market"
     *   value: "US,CA"
     * }
     * documents_metadata_filters {
     *   key: "user"
     *   value: "agent"
     * }
     * ```
     *
     * Generated from protobuf field <code>map<string, string> documents_metadata_filters = 8 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $documents_metadata_filters;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Optional. The unique identifier of this participant.
     *           Format: `projects/<Project ID>/locations/<Location
     *           ID>/conversations/<Conversation ID>/participants/<Participant ID>`.
     *     @type int $role
     *           Immutable. The role this participant plays in the conversation. This field
     *           must be set during participant creation and is then immutable.
     *     @type string $sip_recording_media_label
     *           Optional. Label applied to streams representing this participant in SIPREC
     *           XML metadata and SDP. This is used to assign transcriptions from that
     *           media stream to this participant. This field can be updated.
     *     @type string $obfuscated_external_user_id
     *           Optional. Obfuscated user id that should be associated with the created
     *           participant.
     *           You can specify a user id as follows:
     *           1. If you set this field in
     *              [CreateParticipantRequest][google.cloud.dialogflow.v2.CreateParticipantRequest.participant]
     *              or
     *              [UpdateParticipantRequest][google.cloud.dialogflow.v2.UpdateParticipantRequest.participant],
     *              Dialogflow adds the obfuscated user id with the participant.
     *           2. If you set this field in
     *              [AnalyzeContent][google.cloud.dialogflow.v2.AnalyzeContentRequest.participant]
     *              or
     *              [StreamingAnalyzeContent][google.cloud.dialogflow.v2.StreamingAnalyzeContentRequest.participant],
     *              Dialogflow will update
     *              [Participant.obfuscated_external_user_id][google.cloud.dialogflow.v2.Participant.obfuscated_external_user_id].
     *           Dialogflow returns an error if you try to add a user id for a
     *           non-[END_USER][google.cloud.dialogflow.v2.Participant.Role.END_USER]
     *           participant.
     *           Dialogflow uses this user id for billing and measurement purposes. For
     *           example, Dialogflow determines whether a user in one conversation returned
     *           in a later conversation.
     *           Note:
     *           * Please never pass raw user ids to Dialogflow. Always obfuscate your user
     *             id first.
     *           * Dialogflow only accepts a UTF-8 encoded string, e.g., a hex digest of a
     *             hash function like SHA-512.
     *           * The length of the user id must be <= 256 characters.
     *     @type array|\Google\Protobuf\Internal\MapField $documents_metadata_filters
     *           Optional. Key-value filters on the metadata of documents returned by
     *           article suggestion. If specified, article suggestion only returns suggested
     *           documents that match all filters in their
     *           [Document.metadata][google.cloud.dialogflow.v2.Document.metadata]. Multiple
     *           values for a metadata key should be concatenated by comma. For example,
     *           filters to match all documents that have 'US' or 'CA' in their market
     *           metadata values and 'agent' in their user metadata values will be
     *           ```
     *           documents_metadata_filters {
     *             key: "market"
     *             value: "US,CA"
     *           }
     *           documents_metadata_filters {
     *             key: "user"
     *             value: "agent"
     *           }
     *           ```
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\Participant::initOnce();
        parent::__construct($data);
    }

    /**
     * Optional. The unique identifier of this participant.
     * Format: `projects/<Project ID>/locations/<Location
     * ID>/conversations/<Conversation ID>/participants/<Participant ID>`.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Optional. The unique identifier of this participant.
     * Format: `projects/<Project ID>/locations/<Location
     * ID>/conversations/<Conversation ID>/participants/<Participant ID>`.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OPTIONAL];</code>
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
     * Immutable. The role this participant plays in the conversation. This field
     * must be set during participant creation and is then immutable.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.Participant.Role role = 2 [(.google.api.field_behavior) = IMMUTABLE];</code>
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Immutable. The role this participant plays in the conversation. This field
     * must be set during participant creation and is then immutable.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.Participant.Role role = 2 [(.google.api.field_behavior) = IMMUTABLE];</code>
     * @param int $var
     * @return $this
     */
    public function setRole($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Dialogflow\V2\Participant\Role::class);
        $this->role = $var;

        return $this;
    }

    /**
     * Optional. Label applied to streams representing this participant in SIPREC
     * XML metadata and SDP. This is used to assign transcriptions from that
     * media stream to this participant. This field can be updated.
     *
     * Generated from protobuf field <code>string sip_recording_media_label = 6 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return string
     */
    public function getSipRecordingMediaLabel()
    {
        return $this->sip_recording_media_label;
    }

    /**
     * Optional. Label applied to streams representing this participant in SIPREC
     * XML metadata and SDP. This is used to assign transcriptions from that
     * media stream to this participant. This field can be updated.
     *
     * Generated from protobuf field <code>string sip_recording_media_label = 6 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param string $var
     * @return $this
     */
    public function setSipRecordingMediaLabel($var)
    {
        GPBUtil::checkString($var, True);
        $this->sip_recording_media_label = $var;

        return $this;
    }

    /**
     * Optional. Obfuscated user id that should be associated with the created
     * participant.
     * You can specify a user id as follows:
     * 1. If you set this field in
     *    [CreateParticipantRequest][google.cloud.dialogflow.v2.CreateParticipantRequest.participant]
     *    or
     *    [UpdateParticipantRequest][google.cloud.dialogflow.v2.UpdateParticipantRequest.participant],
     *    Dialogflow adds the obfuscated user id with the participant.
     * 2. If you set this field in
     *    [AnalyzeContent][google.cloud.dialogflow.v2.AnalyzeContentRequest.participant]
     *    or
     *    [StreamingAnalyzeContent][google.cloud.dialogflow.v2.StreamingAnalyzeContentRequest.participant],
     *    Dialogflow will update
     *    [Participant.obfuscated_external_user_id][google.cloud.dialogflow.v2.Participant.obfuscated_external_user_id].
     * Dialogflow returns an error if you try to add a user id for a
     * non-[END_USER][google.cloud.dialogflow.v2.Participant.Role.END_USER]
     * participant.
     * Dialogflow uses this user id for billing and measurement purposes. For
     * example, Dialogflow determines whether a user in one conversation returned
     * in a later conversation.
     * Note:
     * * Please never pass raw user ids to Dialogflow. Always obfuscate your user
     *   id first.
     * * Dialogflow only accepts a UTF-8 encoded string, e.g., a hex digest of a
     *   hash function like SHA-512.
     * * The length of the user id must be <= 256 characters.
     *
     * Generated from protobuf field <code>string obfuscated_external_user_id = 7 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return string
     */
    public function getObfuscatedExternalUserId()
    {
        return $this->obfuscated_external_user_id;
    }

    /**
     * Optional. Obfuscated user id that should be associated with the created
     * participant.
     * You can specify a user id as follows:
     * 1. If you set this field in
     *    [CreateParticipantRequest][google.cloud.dialogflow.v2.CreateParticipantRequest.participant]
     *    or
     *    [UpdateParticipantRequest][google.cloud.dialogflow.v2.UpdateParticipantRequest.participant],
     *    Dialogflow adds the obfuscated user id with the participant.
     * 2. If you set this field in
     *    [AnalyzeContent][google.cloud.dialogflow.v2.AnalyzeContentRequest.participant]
     *    or
     *    [StreamingAnalyzeContent][google.cloud.dialogflow.v2.StreamingAnalyzeContentRequest.participant],
     *    Dialogflow will update
     *    [Participant.obfuscated_external_user_id][google.cloud.dialogflow.v2.Participant.obfuscated_external_user_id].
     * Dialogflow returns an error if you try to add a user id for a
     * non-[END_USER][google.cloud.dialogflow.v2.Participant.Role.END_USER]
     * participant.
     * Dialogflow uses this user id for billing and measurement purposes. For
     * example, Dialogflow determines whether a user in one conversation returned
     * in a later conversation.
     * Note:
     * * Please never pass raw user ids to Dialogflow. Always obfuscate your user
     *   id first.
     * * Dialogflow only accepts a UTF-8 encoded string, e.g., a hex digest of a
     *   hash function like SHA-512.
     * * The length of the user id must be <= 256 characters.
     *
     * Generated from protobuf field <code>string obfuscated_external_user_id = 7 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param string $var
     * @return $this
     */
    public function setObfuscatedExternalUserId($var)
    {
        GPBUtil::checkString($var, True);
        $this->obfuscated_external_user_id = $var;

        return $this;
    }

    /**
     * Optional. Key-value filters on the metadata of documents returned by
     * article suggestion. If specified, article suggestion only returns suggested
     * documents that match all filters in their
     * [Document.metadata][google.cloud.dialogflow.v2.Document.metadata]. Multiple
     * values for a metadata key should be concatenated by comma. For example,
     * filters to match all documents that have 'US' or 'CA' in their market
     * metadata values and 'agent' in their user metadata values will be
     * ```
     * documents_metadata_filters {
     *   key: "market"
     *   value: "US,CA"
     * }
     * documents_metadata_filters {
     *   key: "user"
     *   value: "agent"
     * }
     * ```
     *
     * Generated from protobuf field <code>map<string, string> documents_metadata_filters = 8 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getDocumentsMetadataFilters()
    {
        return $this->documents_metadata_filters;
    }

    /**
     * Optional. Key-value filters on the metadata of documents returned by
     * article suggestion. If specified, article suggestion only returns suggested
     * documents that match all filters in their
     * [Document.metadata][google.cloud.dialogflow.v2.Document.metadata]. Multiple
     * values for a metadata key should be concatenated by comma. For example,
     * filters to match all documents that have 'US' or 'CA' in their market
     * metadata values and 'agent' in their user metadata values will be
     * ```
     * documents_metadata_filters {
     *   key: "market"
     *   value: "US,CA"
     * }
     * documents_metadata_filters {
     *   key: "user"
     *   value: "agent"
     * }
     * ```
     *
     * Generated from protobuf field <code>map<string, string> documents_metadata_filters = 8 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setDocumentsMetadataFilters($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::STRING);
        $this->documents_metadata_filters = $arr;

        return $this;
    }

}
