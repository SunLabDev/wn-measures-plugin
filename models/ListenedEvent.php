<?php namespace SunLab\Measures\Models;

use Model;
use Winter\Storm\Database\Traits\Validation;
use Winter\Storm\Support\Facades\Str;

/**
 * ListenedEvent Model
 */
class ListenedEvent extends Model
{
    protected $eventsList = [
        'backend.page.beforeDisplay',
        'backend.ajax.beforeRunHandler',
        'backend.menu.extendItems',
        'backend.richeditor.listTypes',
        'backend.richeditor.getTypeInfo',
        'backend.user.login',
        'backend.beforeRoute',
        'backend.route',
        'backend.filter.extendQuery',
        'backend.filter.extendScopesBefore',
        'backend.filter.extendScopes',
        'backend.form.beforeRefresh',
        'backend.form.refreshFields',
        'backend.form.refresh',
        'backend.form.extendFieldsBefore',
        'backend.form.extendFields',
        'backend.list.extendQueryBefore',
        'backend.list.extendQuery',
        'backend.list.extendRecords',
        'backend.list.extendColumns',
        'backend.list.overrideHeaderValue',
        'backend.list.overrideColumnValueRaw',
        'backend.list.overrideColumnValue',
        'backend.list.injectRowClass',
        'system.reportwidgets.extendItems',
        'system.resizer.getDefaultOptions',
        'system.resizer.getAvailableSources',
        'system.resizer.processResize',
        'system.resizer.afterResize',
        'system.settings.extendItems',
        'system.console.theme.sync.getAvailableModelClasses',
        'system.console.mirror.extendPaths',
        'system.assets.beforeAddAsset',
        'system.extendConfigFile',
        'media.file.upload',
        'media.file.delete',
        'media.file.rename',
        'media.file.move',
        'media.folder.delete',
        'media.folder.rename',
        'media.folder.create',
        'media.folder.move',
        'model.form.filterFields',
        'model.auth.beforeImpersonate',
        'model.auth.afterImpersonate',
        'model.afterBoot',
        'model.beforeCreate',
        'model.afterCreate',
        'model.beforeUpdate',
        'model.afterUpdate',
        'model.beforeSave',
        'model.afterSave',
        'model.beforeDelete',
        'model.afterDelete',
        'model.beforeFetch',
        'model.afterFetch',
        'model.saveInternal',
        'model.beforeGetAttribute',
        'model.getAttribute',
        'model.beforeSetAttribute',
        'model.setAttribute',
        'model.relation.beforeAttach',
        'model.relation.afterAttach',
        'model.relation.beforeDetach',
        'model.relation.afterDetach',
        'model.beforeRestore',
        'model.afterRestore',
        'model.beforeValidate',
        'model.afterValidate',
        'cms.object.listInTheme',
        'cms.component.beforeRunAjaxHandler',
        'cms.component.runAjaxHandler',
        'cms.page.beforeDisplay',
        'cms.page.display',
        'cms.page.init',
        'cms.page.beforeRenderPage',
        'cms.page.start',
        'cms.page.end',
        'cms.page.postprocess',
        'cms.page.initComponents',
        'cms.page.render',
        'cms.page.beforeRenderPartial',
        'cms.page.renderPartial',
        'cms.page.beforeRenderContent',
        'cms.page.renderContent',
        'cms.ajax.beforeRunHandler',
        'cms.router.beforeRoute',
        'cms.theme.getActiveTheme',
        'cms.theme.setActiveTheme',
        'cms.theme.getEditTheme',
        'cms.theme.extendConfig',
        'cms.theme.extendFormConfig',
        'cms.template.save',
        'cms.template.delete',
        'cms.template.processSettingsAfterLoad',
        'cms.template.processSettingsBeforeSave',
        'cms.template.processTwigContent',
        'cms.beforeRoute',
        'cms.route',
        'cms.block.render',
        'cms.combiner.beforePrepare',
        'cms.combiner.getCacheKey',
        'exception.beforeReport',
        'exception.report',
        'halcyon.datasource.db.extendQuery',
        'halcyon.datasource.db.beforeInsert',
        'halcyon.datasource.db.beforeUpdate',
        'halcyon.datasource.db.beforeGetAvailablePaths',
        'mailer.beforeSend',
        'mailer.prepareSend',
        'mailer.send',
        'mailer.beforeAddContent',
        'mailer.addContent',
        'translator.beforeResolve'
    ];
    use Validation;

    public $table = 'sunlab_measures_listened_events';

    public $rules = [];

    protected $guarded = ['*'];

    protected $fillable = [];

    public $timestamps = false;

    public function getEventTypeAttribute($value)
    {
        if ($value) {
            return $value;
        }

        return $this->exists ? Str::before($this->event_name, '.') : array_first($this->getEventTypeOptions());
    }

    public function getEventTypeOptions()
    {
        $eventsTypes = array_unique(array_map(static function ($eventName) {
            return Str::before($eventName, '.');
        }, $this->eventsList));

        return array_combine($eventsTypes, $eventsTypes);
    }

    public function getEventNameOptions()
    {
        $events = array_filter($this->eventsList, function ($eventName) {
            return Str::startsWith($eventName, $this->_event_type);
        });

        return array_combine($events, $events);
    }

    public function getMeasureNameOptions()
    {
        $measures = Measure::query()
            ->select('name')
            ->distinct('name')
            ->get();

        if ($this->measure_name) {
            $measures->add(['name' => $this->measure_name]);
        }

        return $measures->pluck('name', 'name');
    }
}
