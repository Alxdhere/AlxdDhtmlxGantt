<?php
Yii::import('zii.widgets.CBaseListView');
Yii::import('zii.widgets.grid.CDataColumn');
Yii::import('zii.widgets.grid.CCheckBoxColumn');

class AlxdDhtmlxGantt extends CBaseListView
{
    private $_assets;

    /**
     * @var string|array the table style.
     * Valid values are TbHtml::GRID_TYPE_STRIPED, TbHtml::GRID_TYPE_BORDERED, TbHtml::GRID_TYPE_CONDENSED and/or
     * TbHtml::GRID_TYPE_HOVER.
     */
    public $type;

    /**
     * @var array grid column configuration. Each array element represents the configuration
     * for one particular grid column which can be either a string or an array.
     *
     * When a column is specified as a string, it should be in the format of "name:type:header",
     * where "type" and "header" are optional. A {@link CDataColumn} instance will be created in this case,
     * whose {@link CDataColumn::name}, {@link CDataColumn::type} and {@link CDataColumn::header}
     * properties will be initialized accordingly.
     *
     * When a column is specified as an array, it will be used to create a grid column instance, where
     * the 'class' element specifies the column class name (defaults to {@link CDataColumn} if absent).
     * Currently, these official column classes are provided: {@link CDataColumn},
     * {@link CLinkColumn}, {@link CButtonColumn} and {@link CCheckBoxColumn}.
     */
    public $columns=array();

    /**
     * @var boolean whether to display the table even when there is no data. Defaults to true.
     * The {@link emptyText} will be displayed to indicate there is no data.
     */
    public $showTableOnEmpty=true;

    /**
     * @var mixed the ID of the container whose content may be updated with an AJAX response.
     * Defaults to null, meaning the container for this grid view instance.
     * If it is set false, it means sorting and pagination will be performed in normal page requests
     * instead of AJAX requests. If the sorting and pagination should trigger the update of multiple
     * containers' content in AJAX fashion, these container IDs may be listed here (separated with comma).
     */
    public $ajaxUpdate;

    /**
     * @var string the jQuery selector of the HTML elements that may trigger AJAX updates when they are clicked.
     * These tokens are recognized: {page} and {sort}. They will be replaced with the pagination and sorting links selectors.
     * Defaults to '{page}, {sort}', that means that the pagination links and the sorting links will trigger AJAX updates.
     * Tokens are available from 1.1.11
     *
     * Note: if this value is empty an exception will be thrown.
     *
     * Example (adding a custom selector to the default ones):
     * <pre>
     *  ...
     *  'updateSelector'=>'{page}, {sort}, #mybutton',
     *  ...
     * </pre>
     * @since 1.1.7
     */
    public $updateSelector='{page}, {sort}';

    /**
     * @var string a javascript function that will be invoked if an AJAX update error occurs.
     *
     * The function signature is <code>function(xhr, textStatus, errorThrown, errorMessage)</code>
     * <ul>
     * <li><code>xhr</code> is the XMLHttpRequest object.</li>
     * <li><code>textStatus</code> is a string describing the type of error that occurred.
     * Possible values (besides null) are "timeout", "error", "notmodified" and "parsererror"</li>
     * <li><code>errorThrown</code> is an optional exception object, if one occurred.</li>
     * <li><code>errorMessage</code> is the CGridView default error message derived from xhr and errorThrown.
     * Usefull if you just want to display this error differently. CGridView by default displays this error with an javascript.alert()</li>
     * </ul>
     * Note: This handler is not called for JSONP requests, because they do not use an XMLHttpRequest.
     *
     * Example (add in a call to CGridView):
     * <pre>
     *  ...
     *  'ajaxUpdateError'=>'function(xhr,ts,et,err){ $("#myerrordiv").text(err); }',
     *  ...
     * </pre>
     */
    public $ajaxUpdateError;

    /**
     * @var string the name of the GET variable that indicates the request is an AJAX request triggered
     * by this widget. Defaults to 'ajax'. This is effective only when {@link ajaxUpdate} is not false.
     */
    public $ajaxVar='ajax';

    /**
     * @var mixed the URL for the AJAX requests should be sent to. {@link CHtml::normalizeUrl()} will be
     * called on this property. If not set, the current page URL will be used for AJAX requests.
     * @since 1.1.8
     */
    public $ajaxUrl;
    /**
     * @var string the type ('GET' or 'POST') of the AJAX requests. If not set, 'GET' will be used.
     * You can set this to 'POST' if you are filtering by many fields at once and have a problem with GET query string length.
     * Note that in POST mode direct links and {@link enableHistory} feature may not work correctly!
     * @since 1.1.14
     */
    public $ajaxType;

    /**
     * @var string a javascript function that will be invoked before an AJAX update occurs.
     * The function signature is <code>function(id,options)</code> where 'id' refers to the ID of the grid view,
     * 'options' the AJAX request options  (see jQuery.ajax api manual).
     */
    public $beforeAjaxUpdate;
    /**
     * @var string a javascript function that will be invoked after a successful AJAX response is received.
     * The function signature is <code>function(id, data)</code> where 'id' refers to the ID of the grid view,
     * 'data' the received ajax response data.
     */
    public $afterAjaxUpdate;

    public $onTaskSelected;
    public $onTaskOpened;
    public $onTaskClosed;
    public $onTaskDragStart;
    public $onTaskDrag;
    public $onBeforeTaskDrag;
    public $onBeforeTaskChanged;
    public $onAfterTaskDrag;

    /**
     * @var string the text to be displayed in a data cell when a data value is null. This property will NOT be HTML-encoded
     * when rendering. Defaults to an HTML blank.
     */
    public $nullDisplay='&nbsp;';

    /**
     * @var mixed the Url of the data processor to handler of changing gantt elements
     */
    public $dataProcessorUrl;

    /**
     * @var string the tag name for the container of all data item display. Defaults to 'div'.
     */
    public $itemsTag = 'div';

    /**
     * @var string the style name for the container of all data item display. Defaults to 'height:500px;'.
     */
    public $itemsStyle='height:500px;';

    /**
     * @var array of corresponding attribute names.
     * Mandatory properties
     *   text - ( string ) the task text.
     *   start_date - ( string ) the date when a task is scheduled to begin.
     *   duration - ( number ) the task duration.
     *
     * Details: http://docs.dhtmlx.com/gantt/desktop__loading.html#loadingfromadatabase
     */
    public $taskAttributes = array();

    /**
     * @var string the text to be displayed in an empty grid cell. This property will NOT be HTML-encoded when rendering. Defaults to an HTML blank.
     * This differs from {@link nullDisplay} in that {@link nullDisplay} is only used by {@link CDataColumn} to render
     * null data values.
     * @since 1.1.7
     */
    public $blankDisplay='&nbsp;';

    /**
     * @var string the jQuery selector of filter input fields.
     * The token '{filter}' is recognized and it will be replaced with the grid filters selector.
     * Defaults to '{filter}'.
     *
     * Note: if this value is empty an exception will be thrown.
     *
     * Example (adding a custom selector to the default one):
     * <pre>
     *  ...
     *  'filterSelector'=>'{filter}, #myfilter',
     *  ...
     * </pre>
     * @since 1.1.13
     */
    public $filterSelector='{filter}';

    /**
     * @var string the CSS class name for the table row element containing all filter input fields. Defaults to 'filters'.
     * @see filter
     * @since 1.1.1
     */
    public $filterCssClass='gantt_grid_head_cell';

    /**
     * @var CModel the model instance that keeps the user-entered filter data. When this property is set,
     * the grid view will enable column-based filtering. Each data column by default will display a text field
     * at the top that users can fill in to filter the data.
     * Note that in order to show an input field for filtering, a column must have its {@link CDataColumn::name}
     * property set or have {@link CDataColumn::filter} as the HTML code for the input field.
     * When this property is not set (null) the filtering is disabled.
     * @since 1.1.1
     */
    public $filter;

    /**
     * @var boolean whether to leverage the {@link https://developer.mozilla.org/en/DOM/window.history DOM history object}.  Set this property to true
     * to persist state of grid across page revisits.  Note, there are two limitations for this feature:
     * <ul>
     *    <li>this feature is only compatible with browsers that support HTML5.</li>
     *    <li>expect unexpected functionality (e.g. multiple ajax calls) if there is more than one grid/list on a single page with enableHistory turned on.</li>
     * </ul>
     * @since 1.1.11
     */
    public $enableHistory=false;

    /**
     * @var array of all scales (primary and subscales)
     * http://docs.dhtmlx.com/gantt/api__gantt_scale_unit_config.html
     * http://docs.dhtmlx.com/gantt/api__gantt_subscales_config.html
     */
    public $scales;

    /**
     * @var boolean whether to render tree in gantt grid
     */
    public $tree = false;
    private $tree_column_name = null;

    public function init()
    {
        parent::init();

        if(empty($this->updateSelector))
            throw new CException(Yii::t('zii','The property updateSelector should be defined.'));

        if(empty($this->filterSelector))
            throw new CException(Yii::t('zii','The property filterSelector should be defined.'));

        if (empty($this->itemsTag))
            throw new CException(Yii::t('zii','The property itemsTag should be defined.'));

        if (!is_array($this->taskAttributes))
            throw new CException(Yii::t('zii','The property taskAttributes must be array.'));

        if (!isset($this->taskAttributes['text']))
            throw new CException(Yii::t('zii','The property taskAttributes[\'text\'] should be defined.'));

        if (!isset($this->taskAttributes['start_date']))
            throw new CException(Yii::t('zii','The property taskAttributes[\'start_date\'] should be defined.'));

        if (!isset($this->taskAttributes['duration']) && !isset($this->taskAttributes['end_date']))
            throw new CException(Yii::t('zii','The property taskAttributes[\'duration\'] or taskAttributes[\'end_date\'] should be defined.'));

        if (!is_array($this->scales) || count($this->scales) == 0)
            throw new CException(Yii::t('zii','The property scales should be array and not empty.'));

        if (!is_array($this->scales[0]))
            throw new CException(Yii::t('zii','The each value of scales should be array.'));

        if (!(isset($this->scales[0]['unit']) && isset($this->scales[0]['step']) && isset($this->scales[0]['date'])))
            throw new CException(Yii::t('zii','The each value of scales should be array with \'unit\', \'step\' and \'date\' keys.'));

        $this->initColumns();
    }

    /**
     * Registers necessary client scripts.
     */
    public function registerClientScript()
    {
        $id = $this->getId();

        if($this->ajaxUpdate===false)
            $ajaxUpdate=false;
        else
            $ajaxUpdate=array_unique(preg_split('/\s*,\s*/',$this->ajaxUpdate.','.$id,-1,PREG_SPLIT_NO_EMPTY));

        $itemsSelector = $this->itemsTag;
        $itemsCssClass = explode(' ',$this->itemsCssClass,2);
        if (is_array($itemsCssClass)) {
            $itemsSelector .= '.'.$itemsCssClass[0];
        }

        $options=array(
            'ajaxUpdate'=>$ajaxUpdate,
            'ajaxVar'=>$this->ajaxVar,
            'pagerClass'=>$this->pagerCssClass,
            'loadingClass'=>$this->loadingCssClass,
            'filterClass'=>$this->filterCssClass,
//            'tableClass'=>$this->itemsCssClass,
//            'selectableRows'=>$this->selectableRows,
            'enableHistory'=>$this->enableHistory,
            'updateSelector'=>$this->updateSelector,
            'filterSelector'=>$this->filterSelector,
            'itemsSelector'=>$itemsSelector,
        );
        if($this->ajaxUrl!==null)
            $options['url']=CHtml::normalizeUrl($this->ajaxUrl);
        if($this->ajaxType!==null)
            $options['ajaxType']=strtoupper($this->ajaxType);
        if($this->enablePagination)
            $options['pageVar']=$this->dataProvider->getPagination()->pageVar;
        foreach(array('beforeAjaxUpdate', 
                    'afterAjaxUpdate', 
                    'ajaxUpdateError', 
                    'onTaskSelected', 
                    'onTaskOpened',
                    'onTaskClosed', 
                    'onTaskDragStart', 
                    'onTaskDrag',
                    'onBeforeTaskDrag',
                    'onBeforeTaskChanged',
                    'onAfterTaskDrag',
                    /*, 'selectionChanged'*/) as $event)
        {
            if($this->$event!==null)
            {
                if($this->$event instanceof CJavaScriptExpression)
                    $options[$event]=$this->$event;
                else
                    $options[$event]=new CJavaScriptExpression($this->$event);
            }
        }

        $options['config'] = array(
            //The default date format for JSON and XML data is "%d-%m-%Y" http://docs.dhtmlx.com/gantt/desktop__loading.html#loadingfromadatabase
            'xml_date'=>'%Y-%m-%d',
            'columns'=>array_map(function($column){
                if ($column instanceof CCheckBoxColumn) {
                    $ret = array('name'=>$column->name);
                } elseif ($column instanceof AlxdStatusrefColumn) {
                    $ret = array('name'=>$column->name.($column->format ? '.'.$column->format : ''));
                } elseif ($column instanceof AlxdAttributerefColumn) {
                    $ret = array('name'=>$column->name.($column->attribute ? '.'.$column->attribute : ''));
                } else {
                    $ret = array('name'=>$column->name);
                }

                $r = new ReflectionMethod($column, 'renderHeaderCellContent');
                $r->setAccessible(true);
                ob_start();
                $r->invoke($column);
                $ret['label'] = ob_get_contents();
                ob_end_clean();

                if ($column instanceof CCheckBoxColumn) {
                    $ret['width'] = 36;
                } else {
                    $headerHtmlOptions = $column->headerHtmlOptions;
                    if (isset($headerHtmlOptions['style'])) {
                        $styles = explode(';', rtrim($headerHtmlOptions['style'], ';'));
                        foreach ($styles as $style) {
                            $pair = explode(':', $style, 2);
                            if (count($pair) == 2 && strtolower(trim($pair[0])) == 'width') {
                                $l = strlen($pair[1]);
                                if (strtolower(substr($pair[1], $l-2, 2)) == 'px') {
                                    $ret['width'] = substr($pair[1], 0, $l - 2);
                                }
                            }
                        }
                    }

                    if ($this->tree && $column->name == $this->tree_column_name) {
                        $ret['tree'] = $this->tree;
                    }
                }
                return $ret;
            }, $this->columns),
            'filters'=>array_map(function($column){
                $r = new ReflectionMethod($column, 'renderFilterCellContent');
                $r->setAccessible(true);
                ob_start();
                $r->invoke($column);
                $filter = ob_get_contents();
                ob_end_clean();

                return array(
                    'name'=>$column->name,
                    'control'=>$filter
                );
            }, $this->columns),
            'data'=>$this->getData(),
        );

        $options['config']['scale_unit'] = $this->scales[0]['unit'];
        $options['config']['date_scale'] = $this->scales[0]['date'];
        if (count($this->scales) > 1) {
            $options['config']['subscales'] = array_slice($this->scales, 1);
        }

        if ($this->filter !== null) {
            $options['config']['scale_height_auto'] = true;
            $options['config']['filter'] = true;
        }

        if (isset($this->dataProcessorUrl)) {
            $options['dataProcessorUrl'] = $this->dataProcessorUrl;
        }

        $options=CJavaScript::encode($options);
        $cs=Yii::app()->getClientScript();
        if ($this->_assets == null) {
            $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
            $this->_assets = Yii::app()->assetManager->publish($path);
        }

        $cs->registerCoreScript('jquery');
        $cs->registerCoreScript('bbq');

        if($this->enableHistory)
            $cs->registerCoreScript('history');

        $cs->registerCssFile($this->_assets . DIRECTORY_SEPARATOR . 'dhtmlxgantt.css');
        $cs->registerScriptFile($this->_assets . DIRECTORY_SEPARATOR . 'dhtmlxgantt.js', CClientScript::POS_BEGIN);
        $cs->registerScriptFile($this->_assets . DIRECTORY_SEPARATOR . 'locale/locale_'.Yii::app()->language.'.js', CClientScript::POS_BEGIN);
        $cs->registerScriptFile($this->_assets . DIRECTORY_SEPARATOR . 'alxd.dhtmlxgantt.js', CClientScript::POS_BEGIN);
        $cs->registerScript(__CLASS__.'#'.$id,"jQuery('#$id').alxdDhtmlxGantt($options);", CClientScript::POS_READY);
    }

    /**
     * Renders the data items for the grid view.
     */
    public function renderItems()
    {
        if($this->dataProvider->getItemCount()>0 || $this->showTableOnEmpty)
        {
            echo CHtml::openTag($this->itemsTag, array('class'=>$this->itemsCssClass, 'style'=>$this->itemsStyle));
            //render container only
            //content render in javascript
            echo CHtml::closeTag($this->itemsTag);
        }
        else
            $this->renderEmptyText();
    }

    /**
     * Creates column objects and initializes them.
     */
    protected function initColumns()
    {
        if($this->columns===array())
        {
            if($this->dataProvider instanceof CActiveDataProvider)
                $this->columns=$this->dataProvider->model->attributeNames();
            elseif($this->dataProvider instanceof IDataProvider)
            {
                // use the keys of the first row of data as the default columns
                $data=$this->dataProvider->getData();
                if(isset($data[0]) && is_array($data[0]))
                    $this->columns=array_keys($data[0]);
            }
        }
        $id=$this->getId();
        foreach($this->columns as $i=>$column)
        {
            if(is_string($column))
                $column=$this->createDataColumn($column);
            else
            {
                if(!isset($column['class'])) {
                    $column['class'] = 'CDataColumn';
                }
                $column=Yii::createComponent($column, $this);
            }
            if(!$column->visible)
            {
                unset($this->columns[$i]);
                continue;
            }
            if($column->id===null)
                $column->id=$id.'_c'.$i;
            $this->columns[$i]=$column;
        }

        $tree_initiated = false;
        foreach($this->columns as $column) {
            $column->init();

            if ($column instanceof CDataColumn && $this->tree && !$tree_initiated) {
                $this->tree_column_name = $column->name;
                $tree_initiated = true;
            }
        }
    }

    /**
     * Return the data items for the gantt view.
     */
    public function getData()
    {
        $ret = array('data'=>array());
        $data = $this->dataProvider->getData();
        $n = count($data);
        if($n > 0) {
            for($row=0; $row < $n; ++$row)
                $ret['data'][] = $this->getDataRow($row);
        }
        return $ret;
    }

    public function getDataRow($row)
    {
        $ret = array();
//        $htmlOptions=array();
//        if($this->rowHtmlOptionsExpression!==null)
//        {
            $data=$this->dataProvider->data[$row];
//            $options=$this->evaluateExpression($this->rowHtmlOptionsExpression,array('row'=>$row,'data'=>$data));
//            if(is_array($options))
//                $htmlOptions = $options;
//        }
//
//        if($this->rowCssClassExpression!==null)
//        {
//            $data=$this->dataProvider->data[$row];
//            $class=$this->evaluateExpression($this->rowCssClassExpression,array('row'=>$row,'data'=>$data));
//        }
//        elseif(is_array($this->rowCssClass) && ($n=count($this->rowCssClass))>0)
//            $class=$this->rowCssClass[$row%$n];
//
//        if(!empty($class))
//        {
//            if(isset($htmlOptions['class']))
//                $htmlOptions['class'].=' '.$class;
//            else
//                $htmlOptions['class']=$class;
//        }

//        echo CHtml::openTag('tr', $htmlOptions)."\n";
//        $startDt = $data->getAttributeref('tsk_task_plan_startdate')->value;
//        if (is_null($startDt)) {
////            $ret['start_date'] = null;
//        } else {
//            $startDt = new DateTime($startDt);
//            $ret['start_date'] = $startDt->format('d-m-Y');
//        }
//
//        $endDt = $data->getAttributeref('tsk_task_plan_enddate')->value;
//        if (is_null($endDt)) {
//            $ret['duration'] = 0;
//        } else {
//            $endDt = new DateTime($endDt);
//            if (is_null($startDt)) {
//                $ret['start_date'] = $endDt->format('d-m-Y');
//                $ret['duration'] = 0;
//            } else {
//                $ret['duration'] = $endDt->diff($startDt)->days;
////                $ret['duration'] = $endDt->diff($startDt)->h;
//            }
//        }

//http://docs.dhtmlx.com/gantt/samples/common/data.json
//http://docs.dhtmlx.com/gantt/samples/common/testdata.js
//            $tasks = array(
//                'data'=>array(
//                    array(
//                        'id'=>1,
//                        'text'=>"Project #1",
//                        'start_date'=>"01-04-2013",
//                        'duration'=>11,
//                        'progress'=>0.6,
//                        'open'=>true,
//                        'tsk_task_code'=>'ЗАДАЧА-001100'
        //'unscheduled'=>true/false
        //color:"blue", parent:1
//                    )
//                )
//            );
        $ret['id'] = $data['id'];

        foreach ($this->taskAttributes as $taskKey => $taskAttribute) {
            $ret[$taskKey] = CHtml::value($data, $taskAttribute);
        }

        if ($this->tree) {
            if ($this->dataProvider instanceof CActiveDataProvider) {
                if ($data->relParentObjectrefs) {
                    $ret['parent'] = $data->relParentObjectrefs[0]['id'];
                }
            } else {
                if (is_array($data['parents'])) {
                    $ret['parent'] = $data['parents'][0]['id'];
                }
            }
        }

        if (empty($ret['start_date']) && empty($ret['duration']) && empty($ret['end_date'])) {
            $ret['unscheduled'] = true;
        }

        foreach($this->columns as $column) {
            if ($column instanceof CCheckBoxColumn) {
                $r = new ReflectionMethod($column, 'renderDataCellContent');
                $r->setAccessible(true);
                ob_start();
                $r->invoke($column, $row, $data);
                $ret[$column->name] = ob_get_contents();
                ob_end_clean();
            } elseif ($column instanceof AlxdStatusrefColumn) {
                $r = new ReflectionMethod($column, 'getDataCellContent');
                $r->setAccessible(true);
                $value = $r->invoke($column, $row, $data);
                $ret[$column->name.($column->format ? '.'.$column->format : '')] = $value;
            } elseif ($column instanceof AlxdAttributerefColumn) {
                $r = new ReflectionMethod($column, 'getDataCellContent');
                $r->setAccessible(true);
                $value = $r->invoke($column, $row, $data);
                $ret[$column->name.($column->attribute ? '.'.$column->attribute : '')] = $value;
//                $ret[$column->name.($column->multiple ? '.'.($column->format ? $column->format : $column->attribute) : '')] = $value;
            } else {
                $r = new ReflectionMethod($column, 'getDataCellContent');
                $r->setAccessible(true);
                $value = $r->invoke($column, $row, $data);
                $ret[$column->name] = $value;
            }
        }

        return $ret;
    }

}
