<?php
declare(strict_types=1);

namespace App\Services\Social;

/**
 * One social platform. Registered in Channels::ids() order. Implementations extend AbstractChannel (it provides meta(), id(), validate() and a few
 * helpers) and add post() and test(). Contract and field lists: docs/SOCIAL.md.
 *
 * $config passed to test() / post() is SocialConfig::channelConfig($id): [fieldKey => string] with the SECRETS IN PLAINTEXT (adapters only; it must never be
 * logged, returned, stored in the queue or put in an error message). Missing values are ''.
 */
interface ChannelInterface
{
    public function id(): string;

    /**
     * Static description: label, icon, limits, fields, steps ... (see AbstractChannel::DEFAULT_META for every key and docs/SOCIAL.md section 4).
     * @return array<string,mixed>
     */
    public function meta(): array;

    /**
     * Format check of the values an admin typed (no network): fieldKey => message for every non-empty value that is malformed. Empty values are not
     * errors here (a channel may be saved half finished; SocialConfig::ready() says what is still missing).
     * @param array<string,string> $input
     * @return array<string,string>
     */
    public function validate(array $input): array;

    /**
     * TEST CONNECTION: a READ-ONLY call that proves the credentials work (Graph /me, X /2/users/me, LinkedIn organisationAcls, Telegram getMe + getChat, webhook
     * ping with "test": true). Never posts. Must not throw.
     * @param array<string,string> $config
     * @return array{ok:bool,state:string,message:string,detail?:string}  state: ok | auth | error ; message = plain language for the admin (no tokens) ; detail = e.g. the account name
     */
    public function test(array $config): array;

    /**
     * Post ONE issue. Must not throw (catch and return SocialResult::review / failed). Respect $payload->deadline. When $payload->test is true post a short
     * clearly-labelled test message (Telegram: to the test chat when configured; webhook: payload "test": true).
     * @param array<string,string> $config
     */
    public function post(array $config, SocialPayload $payload): SocialResult;
}
