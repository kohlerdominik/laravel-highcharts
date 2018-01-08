<?php


namespace Dokohler\Highcharts;


class Highcharts {


    /**
     * The charts name
     * @var string
     */
    protected $name = NULL;

    /**
     * The container div holding the chart
     * @var string
     */
    protected $container = NULL;


    /**
     * The Chart Type: chart or stockChart
     * @var string
     */
    protected $type = NULL;


    /**
     * Holds all attributes of the chart as dotted array
     * @var array
     */
    protected $attributes = [];


    /**
     * Holds all the series
     * @var array
     */
    protected $series = NULL;




    /**
     * Holds all options of the chart as dotted array
     * @var string
     */
    protected $options = [];



    /**
     * Holds the information, if the options where already rendered (because they must be rendered only once)
     * @var string
     */
    protected $options_rendered = false;


    /**
     * Holds the information, if the options where already rendered (because they must be rendered only once)
     * @var string
     */
    protected $grid = "
        @variables
        function @chartfunction () {  
            @chartname = @chartcontent;
        }
        @chartloader";



    /**
     * Chart constructor.
     *
     * @param $name
     * @param $container
     * @param $type
     * @param $options
     */
    public function __construct($name = NULL, $container = NULL, $type = NULL, $options = NULL)
    {
        // set variable the chart is stored in
        $this->name = $name ?? 'MyChart';
        // set container
        $this->container = $container ?? str_random('6');

        // set type
        $this->type = $type ?? 'chart';

        // load default options set
        if ($options) {
                $this->setOptions($options);
            }

        $this->series = collect();
    }



    /**
     * Load the option set from config, translate it and set as class variable
     *
     * @param string $name
     */
    public function loadOptionSet(string $name = 'default')
    {
        // load options_set from config
        $options_set =  config("charts.option_sets.{$name}");

        // translate lang-option
        $options_set['lang'] = $this->translateOptionSet($options_set['lang']);

        // set options_set as class variable
        $this->setOptions($options_set);
    }



    /**
     * Returns the lang options translated
     * Uses the lang file stored in resources/lang/{lang}/charts.php
     *
     * @param array $lang
     * @return array
     */
    protected function translateOptionSet(array $lang)
    {
        // iterate over the array and set every value to translated one
        foreach ($lang as $key => $value) {
            $lang[$key] = __("charts.{$key}");
        }
        return $lang;
    }



    /**
     * Loads a predefined attribute set from config/charts.php
     *
     * @param string $name The attribute sets name
     * @return Highcharts
     */
    public function loadAttributeSet(string $name)
    {
        // load attributes from config
        $attribute_set = \Config::get("charts.attribute_sets.{$name}");

        $attribute_set['series'] = "%series_variable%";

        // set attributes as class_variable
        $this->setAttributes($attribute_set);

        return $this;
    }



    /**
     * Returns entries of dotted variable as multidimensional array
     *
     * @param string $var
     * @return array
     */
    public function getDottedVariable($var) : array
    {
        //setup return var
        $content = [];

        // restore multi dimensional array
        foreach ($this->$var as $key => $value) {
            array_set($content, $key, $value);
        }

        // and return the content
        return $content;
    }



    /**
     * Override all entries of a dotted variable
     *
     * @param string $var
     * @param array $content
     * @return Highcharts
     */
    public function setDottedVariable($var, array $content)
    {
        // convert multidimensional array to doted array
        // and store in attributes
        $this->$var = array_dot($content);

        return $this;
    }



    /**
     * Adds one or multiple entries to a dotted variable
     *
     * @param string $var
     * @param array $content
     * @return Highcharts
     */
    public function addToDottedVariable($var, array $content)
    {
        // convert multidimensional array to doted array
        // and add to attributes
        $this->$var += array_dot($content);
        return $this;
    }



    /**
     * Remove entries from a dotted variable
     *
     * @param array $needles Needles in doted notification
     * @return Highcharts
     */
    public function removeFromDottedVariable($var, array $needles)
    {
        // iterate every needle and unset it if it exists
        foreach ($needles as $i => $key) {

            if (isset($this->$var[$key]) ) {
                unset($this->$var[$key]);
            }
        } //fore


        return $this;
    }


    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


    public function getName($name)
    {
        return $this->name;
    }


    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }

    public function getContainer()
    {
        return $this->container;
    }



    /**
     * Returns attributes as multidimensional array
     *
     * @return array
     */
    public function getAttributes(): array
    {
        $attributes = $this->getDottedVariable('attributes');
        if (!key_exists('series', $attributes)) {
            $attributes['series'] = '%series_variable%';
        }
        return $attributes;
    }


    /**
     * Override all attributes
     *
     * @param array $attributes
     * @return Highcharts
     */
    public function setAttributes(array $attributes)
    {
        return $this->setDottedVariable('attributes', $attributes);
    }



    /**
     * Adds one or multiple attributes
     *
     * @param array $attributes
     * @return Highcharts
     */
    public function addAttributes(array $attributes)
    {
        return $this->addToDottedVariable('attributes', $attributes);
    }



    /**
     * Remove attributes
     *
     * @param array $needles Needles in doted notification
     * @return Highcharts
     */
    public function removeAttributes(array $needles)
    {
        return $this->removeFromDottedVariable('attributes', $needles);
    }



    /**
     * Returns options as multidimensional array
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->getDottedVariable('options');
    }



    /**
     * Override all options
     *
     * @param array $options
     * @return Highcharts
     */
    public function setOptions($options)
    {
        if (is_object($options)) {$options = $options->toArray();}
        return $this->setDottedVariable('options', $options);
    }



    /**
     * Adds one or multiple options
     *
     * @param array $options
     * @return Highcharts
     */
    public function addOptions(array $options)
    {
        return $this->addToDottedVariable('options', $options);
    }



    /**
     * Remove options
     *
     * @param array $needles Needles in doted notification
     * @return Highcharts
     */
    public function removeOptions(array $needles)
    {
        return $this->removeFromDottedVariable('options', $needles);
    }




    /**
     * Set axis with defined ID
     *
     * @param $axis_type
     * @param $axis_id
     * @param array $axis_attributes
     * @return Highcharts
     */
    public function setAxis($axis_type, $axis_id, $axis_attributes = [])
    {
        // check if axis type is valid, or throw error
        if (! in_array($axis_type, ['xAxis', 'yAxis', 'zAxis'])) {
            throw new \InvalidArgumentException('Invalid Axis Type');
        }

        // set axis with type and id
        $this->addAttributes([ $axis_type => [
            $axis_id => $axis_attributes
        ]]);

        return $this;
    }


    public function getAxisId($axis , $key, $value)
    {
        $attributes = $this->getAttributes();

        foreach($attributes[$axis] ?? [] as $axis_id => $axis_attr)
        {
            if(key_exists($key, $axis_attr) AND $axis_attr[$key] == $value) {
                return $axis_id;
            }
        }

        return NULL;
    }



    /**
     * Add a new series
     *
     * @param string $name The series name
     * @param string $type The series type
     * @param array $data The series data
     * @param array $additional_attributes The series additional attributes
     * @return Highcharts
     */
    public function addSeries($name, $type, $data, $additional_attributes = [])
    {
        //preset attributes variable
        $attributes = [];

        // restore multi dimensional array
        foreach ($additional_attributes as $key => $value) {
            array_set($attributes, $key, $value);
        }

        // add series to the series array
        $this->series->push([
            'data' => $data,
            'attributes' =>
                $attributes + [
                    'name' => $name,
                    'type' => $type]]);

        return $this;
    }


    public function addLazySeries($name, $type, $source, $additional_attributes = [])
    {
        //preset attributes variable
        $attributes = [];

        // restore multi dimensional array
        foreach ($additional_attributes as $key => $value) {
            array_set($attributes, $key, $value);
        }

        // add series to the series array
        $this->series->push([
                'source' => $source,
                'attributes' =>
                    $attributes + [
                    'name' => $name,
                    'type' => $type]]);

        return $this;
    }




    /**
     * Returns the rendered Chart with options, if necessary
     *
     * @return string The rendered Chart
     */
    public function render()
    {
        $chart = $this->grid;

        $chart = $this->presetVariables($chart);

        $chart = $this->renderChartMethod($chart);

        $chart = $this->renderChartLoader($chart);

        return $chart;
    }


    public function presetVariables($chart)
    {

        $series = str_replace('\/', '/', $this->series);


        $variables =
            "var {$this->name}Chart,"
            ."{$this->name}RawSeries = {$series},"
            ."{$this->name}SeriesCounter = 0,"
            ."{$this->name}ProcessedSeries = [];";

        return strtr($chart, ["@variables" => $variables]);

    }


    /**
     * Returns the rendered Chart
     *
     * @return string The rendered Chart
     */
    public function renderChartMethod($chart)
    {
        // define chart method
        $chart_function = "load{$this->name}Chart";

        // define chart content header
        switch ($this->type) {
            case 'stockChart': { $content_header = "Highcharts.stockChart('{$this->container}',"; break;}
            default: { $content_header = "Highcharts.chart('{$this->container}',"; break;}
        }

        $attributes =  json_encode(array_merge($this->getOptions(), $this->getAttributes()));
        $chart_body = str_replace('"%series_variable%"', "{$this->name}ProcessedSeries", $attributes);

        // Concat the chart
        $chart_content = $content_header;
        $chart_content .= $chart_body;
        $chart_content .= ");";


        return strtr($chart, [
            "@chartfunction" => $chart_function,
            "@chartname" => "{$this->name}Chart",
            "@chartcontent" => $chart_content .  " console.log({$this->name}ProcessedSeries);"
        ]);

    }



    public function renderChartLoader($chart)
    {

        if ($this->series->isNotEmpty())
        {
            return $this->renderSeries($chart);
        }

        return strtr($chart, ["@chartloader" => "load{$this->name}Chart()"]);
    }


    /**
     * Returns the standard series
     *
     * @return string The rendered Chart
     */
    public function renderSeries($chart)
    {
        $series = "
        function processRawSeries() {
            {$this->name}SeriesCounter = 0;
        
            $.each({$this->name}RawSeries, function(i, rawSeries) {
                    {$this->name}ProcessedSeries[i] = rawSeries['attributes'];
                    if (typeof rawSeries['source'] !== 'undefined') {
                        $.getJSON(rawSeries['source'], function(data) {
                            {$this->name}ProcessedSeries[i]['data'] = data;
                            {$this->name}SeriesCounter += 1;
                            if ({$this->name}SeriesCounter === {$this->name}RawSeries.length) { 
                                load{$this->name}Chart(); }
                        });
                    }
                    else {
                        {$this->name}ProcessedSeries[i]['data'] = rawSeries['data'];
                        {$this->name}SeriesCounter += 1;
                        if ({$this->name}SeriesCounter === {$this->name}RawSeries.length) { 
                            load{$this->name}Chart(); }
                    }
            });
        }
        
        processRawSeries();";

        return strtr($chart, ["@chartloader" => $series]);
    }




}