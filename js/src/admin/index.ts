import app from 'flarum/admin/app';

app.initializers.add('flarum-akismet', () => {
  app.extensionData
    .for('flarum-akismet')
    .registerSetting({
      setting: 'flarum-akismet.api_key',
      type: 'text',
      label: app.translator.trans('flarum-akismet.admin.akismet_settings.api_key_label'),
    })
    .registerPermission(
      {
        icon: 'fas fa-vote-yea',
        label: app.translator.trans('flarum-akismet.admin.permissions.bypass_akismet'),
        permission: 'bypassAkismet',
      },
      'start'
    );
});
