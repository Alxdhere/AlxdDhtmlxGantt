AlxdDhtmlxGantt
===============

DHTMLX Gantt component for Yii 1.x (see http://docs.dhtmlx.com/gantt/)

Simple example (from class):

```php
$cntCommands = $provider->totalItemCount;

$options = array(
    'id' => 'viewpub_grid',
    'dataProcessorUrl'=>Yii::app()->createUrl('controller/action', array('param1'=>$param1, 'param2'=>$param2)),
    'dataProvider' => $provider,
    'filter' => $filter,
    'columns' => $columns,
    'taskAttributes'=> $taskAttributes,
    'itemsCssClass' => 'gantt-mono-primary',
    'summaryCssClass'=>'hidden-xs table-summary',
    'pagerCssClass'=>'table-pagination',
    'loadingCssClass'=>'gantt-loading',
    'enableSorting' => $sorting,
    'tree' => true,
    'scales' =>$viewpub->getScales(),
    'pager' => array(
        'class' => 'CLinkPager',
        'maxButtonCount' => $isMobileClient ? 3 : 10,
        'firstPageLabel' => '&nbsp;<i class="fa fa-angle-double-left"></i>&nbsp;',
        'header' => '',
        'hiddenPageCssClass' => 'disabled',
        'lastPageLabel' => '&nbsp;<i class="fa fa-angle-double-right"></i>&nbsp;',
        'nextPageLabel' => '&nbsp;<i class="fa fa-angle-right"></i>&nbsp;',//'&gt;',
        'selectedPageCssClass' => 'active',
        'prevPageLabel' => '&nbsp;<i class="fa fa-angle-left"></i>&nbsp;',//'&lt;',
        'htmlOptions' => array('class' => 'pagination')
    ),
    'updateSelector' => ($showAll ? '{page}, {sort}, a.show-all' : '{page},{sort}'),
);

if ($cntCommands) {
    $options['afterAjaxUpdate'] = 'function() { $(":checkbox").uniform();}';
    $options['onTaskSelected'] = $options['onTaskOpened'] = $options['onTaskClosed'] = $options['onTaskDrag'] = 'function(id) { $(":checkbox").uniform();}';
}

$this->widget('ext.AlxdDhtmlxGantt.AlxdDhtmlxGantt', $options);
```
