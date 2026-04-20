<div class="layout-row">
    <h3>Cloudflare Turnstile Connection</h3>
    <p>Provide Cloudflare account credentials once and this will create or update a Turnstile widget and store keys in FrameworC settings.</p>

    <?= Form::ajax('onProvisionTurnstile', ['flash' => true, 'class' => 'form-elements']) ?>
        <div class="form-group">
            <label for="account_id">Cloudflare Account ID</label>
            <input
                id="account_id"
                name="account_id"
                type="text"
                class="form-control"
                value="<?= e($formDefaults['account_id']) ?>"
                required
            >
            <p class="help-block">
                Find it here:
                <a href="https://developers.cloudflare.com/fundamentals/account/find-account-and-zone-ids/" target="_blank" rel="noopener noreferrer">
                    Find account and zone IDs
                </a>
            </p>
        </div>

        <div class="form-group">
            <label for="api_token">Cloudflare API Token</label>
            <input
                id="api_token"
                name="api_token"
                type="password"
                class="form-control"
                value="<?= e($formDefaults['api_token']) ?>"
                required
            >
            <p class="help-block">
                Required permissions: Turnstile Sites Write (or Account Settings Write). Create token:
                <a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank" rel="noopener noreferrer">
                    Cloudflare API Tokens
                </a>
                or
                <a href="https://developers.cloudflare.com/fundamentals/api/get-started/create-token/" target="_blank" rel="noopener noreferrer">
                    token creation guide
                </a>.
            </p>
        </div>

        <div class="form-group">
            <label for="widget_name">Turnstile Widget Name</label>
            <input
                id="widget_name"
                name="widget_name"
                type="text"
                class="form-control"
                value="<?= e($formDefaults['widget_name']) ?>"
                required
            >
            <p class="help-block">
                Turnstile docs:
                <a href="https://developers.cloudflare.com/turnstile/" target="_blank" rel="noopener noreferrer">
                    Turnstile overview
                </a>
            </p>
        </div>

        <div class="form-group">
            <label for="domains">Domains (one per line)</label>
            <textarea
                id="domains"
                name="domains"
                class="form-control"
                rows="4"
                required><?= e($formDefaults['domains']) ?></textarea>
        </div>

        <div class="form-group">
            <label>
                <input
                    type="checkbox"
                    name="save_credentials"
                    value="1"
                    <?= $formDefaults['save_credentials'] ? 'checked' : '' ?>
                >
                Save Account ID and API token for future refresh
            </label>
        </div>

        <?php if ($currentSiteKey): ?>
            <div class="callout callout-info no-subheader">
                <div class="content">
                    Current site key: <code><?= e($currentSiteKey) ?></code>
                </div>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Connect and Provision Keys</button>
        <a href="/admin/system/settings/update/crscompany/frameworc/settings#primarytab-integrace" class="btn btn-default">Back to Settings</a>
    <?= Form::close() ?>
</div>
