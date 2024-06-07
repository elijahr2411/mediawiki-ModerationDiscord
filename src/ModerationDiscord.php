<?php
use MediaWiki\MediaWikiServices;

class ModerationDiscord {
    public static function onModerationPending(array $fields, $id) {
        $config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'ModerationDiscord' );
        $wikibase = $config->get( 'Server' ) . $config->get( 'ScriptPath' );
        $webhook = $config->get( 'ModerationDiscordWebhook' );
        $datetime = new DateTime();

        if ($webhook == "") {
            return;
        }

        $data = [
            "embeds" => [
                [
                    "title" => "New Edit Requires Review",
                    "fields" => [
                        [
                            "name" => "Page",
                            "value" => $fields['mod_title']
                        ],
                        [
                            "name" => "User",
                            "value" => $fields["mod_user_text"]
                        ],
                        [
                            "name" => "Actions",
                            "value" => "[View](" . $wikibase . "/index.php?title=Special:Moderation&modaction=show&modid=" . $id . ")"
                        ]
                    ],
                    "timestamp" => $datetime->format(DateTime::ATOM)
                ]
            ]
        ];

        $ch = curl_init($webhook);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Accept: application/json"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}