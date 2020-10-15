<?php
    namespace core\view;

    class Templating{
        
        protected $filters = [];  //To be loaded from Config
        protected $data = [];
        protected $dataContexts = [];
        protected $tempData = null;
        protected $noparseBlocks = [];
        public $leftDelimiter = '{';
        public $rightDelimiter = '}';
        public function replaceVariables(&$view, $variables){

            $view = preg_replace_callback('/(\{)(\{)((?:[a-zA-Z]*))(\})(\})/',
            function($match) use($variables){
                $temporary_match;
                if(isset($variables[$match[3]])){
                    $temporary_match=$variables[$match[3]];
                }else{
                    $temporary_match=null;
                }
                return $temporary_match;
            }, $view);

        }

        public function parse($view, $variables){

            $this->replaceVariables($view, $variables);
            $r=$this->parseCon($view, $variables);
            return $r;
        }

        // protected function parse(string $template, array $data = [], array $options = null): string
        protected function parseCon(string $template, array $data = []): string
        {
            if ($template === '')
            {
                return '';
            }

            // Remove any possible PHP tags since we don't support it
            // and parseConditionals needs it clean anyway...
            $template = str_replace(["<php>", "</php>"], ["<?php", "?>"], $template);//&lt;?  ?&gt;

            // $template = $this->parseComments($template);
            // $template = $this->extractNoparse($template);

            // Replace any conditional code here so we don't have to parse as much
            $template = $this->parseConditionals($template,$data);

            // Handle any plugins before normal data, so that
            // it can potentially modify any template between its tags.
            // $template = $this->parsePlugins($template);

            // loop over the data variables, replacing
            // the content as we go.
            
            foreach ($data as $key => $val)
            {
                $escape = true;

                if (is_array($val))
                {
                    $escape  = false;
                    $replace = $this->parsePair($key, $val, $template);
                }
                else
                {
                    $replace = $this->parseSingle($key, (string) $val);
                }

                foreach ($replace as $pattern => $content)
                {
                    $template = $this->replaceSingle($pattern, $content, $template, $escape);
                }
            }

            return $this->insertNoparse($template);
        }

        //--------------------------------------------------------------------

        /**
         * Parse a single key/value, extracting it
         *
         * @param  string $key
         * @param  string $val
         * @return array
         */
        protected function parseSingle(string $key, string $val): array
        {
            $pattern = '#' . $this->leftDelimiter . '!?\s*' . preg_quote($key) . '\s*\|*\s*([|\w<>=\(\),:.\-\s\+\\/]+)*\s*!?' . $this->rightDelimiter . '#ms';

            return [$pattern => $val];
        }


        //--------------------------------------------------------------------

        /**
         * Parse a tag pair
         *
         * Parses tag pairs: {some_tag} string... {/some_tag}
         *
         * @param  string $variable
         * @param  array  $data
         * @param  string $template
         * @return array
         */
        protected function parsePair(string $variable, array $data, string $template): array
        {
            // Holds the replacement patterns and contents
            // that will be used within a preg_replace in parse()
            $replace = [];

            // Find all matches of space-flexible versions of {tag}{/tag} so we
            // have something to loop over.
            preg_match_all(
                    '#' . $this->leftDelimiter . '\s*' . preg_quote($variable) . '\s*' . $this->rightDelimiter . '(.+?)' .
                    $this->leftDelimiter . '\s*' . '/' . preg_quote($variable) . '\s*' . $this->rightDelimiter . '#s', $template, $matches, PREG_SET_ORDER
            );

            /*
            * Each match looks like:
            *
            * $match[0] {tag}...{/tag}
            * $match[1] Contents inside the tag
            */
            foreach ($matches as $match)
            {
                // Loop over each piece of $data, replacing
                // it's contents so that we know what to replace in parse()
                $str = '';  // holds the new contents for this tag pair.
                foreach ($data as $row)
                {
                    // Objects that have a `toArray()` method should be
                    // converted with that method (i.e. Entities)
                    if (is_object($row) && method_exists($row, 'toArray'))
                    {
                        $row = $row->toArray();
                    }
                    // Otherwise, cast as an array and it will grab public properties.
                    else if (is_object($row))
                    {
                        $row = (array)$row;
                    }

                    $temp  = [];
                    $pairs = [];
                    $out   = $match[1];
                    foreach ($row as $key => $val)
                    {
                        // For nested data, send us back through this method...
                        if (is_array($val))
                        {
                            $pair = $this->parsePair($key, $val, $match[1]);

                            if (! empty($pair))
                            {
                                $pairs[array_keys( $pair )[0]] = true;
                                $temp                          = array_merge($temp, $pair);
                            }

                            continue;
                        }
                        else if (is_object($val))
                        {
                            $val = 'Class: ' . get_class($val);
                        }
                        else if (is_resource($val))
                        {
                            $val = 'Resource';
                        }

                        $temp['#' . $this->leftDelimiter . '!?\s*' . preg_quote($key) . '\s*\|*\s*([|\w<>=\(\),:.\-\s\+\\\\/]+)*\s*!?' . $this->rightDelimiter . '#s'] = $val;
                    }

                    // Now replace our placeholders with the new content.
                    foreach ($temp as $pattern => $content)
                    {
                        $out = $this->replaceSingle($pattern, $content, $out, ! isset( $pairs[$pattern] ) );
                    }

                    $str .= $out;
                }

                //Escape | character from filters as it's handled as OR in regex
                $escaped_match = preg_replace('/(?<!\\\\)\\|/', '\\|', $match[0]);

                $replace['#' . $escaped_match . '#s'] = $str;
            }

            return $replace;
        }


    //--------------------------------------------------------------------

        /**
         * Handles replacing a pseudo-variable with the actual content. Will double-check
         * for escaping brackets.
         *
         * @param $pattern
         * @param $content
         * @param $template
         * @param boolean  $escape
         *
         * @return string
         */
        protected function replaceSingle($pattern, $content, $template, bool $escape = false): string
        {
            // Any dollar signs in the pattern will be mis-interpreted, so slash them
            $pattern = addcslashes($pattern, '$');

            // Replace the content in the template
            $template = preg_replace_callback($pattern, function ($matches) use ($content, $escape) {
                // Check for {! !} syntax to not-escape this one.
                if (strpos($matches[0], '{!') === 0 && substr($matches[0], -2) === '!}')
                {
                    $escape = false;
                }

                return $this->prepareReplacement($matches, $content, $escape);
            }, $template);

            return $template;
        }
        
        //--------------------------------------------------------------------

        /**
         * Callback used during parse() to apply any filters to the value.
         *
         * @param array   $matches
         * @param string  $replace
         * @param boolean $escape
         *
         * @return string
         */
        protected function prepareReplacement(array $matches, string $replace, bool $escape = true): string
        {
            $orig = array_shift($matches);

            // Our regex earlier will leave all chained values on a single line
            // so we need to break them apart so we can apply them all.
            $filters = isset($matches[0]) ? explode('|', $matches[0]) : [];

            if ($escape && ! isset($matches[0]))
            {
                if ($context = $this->shouldAddEscaping($orig))
                {
                    $filters[] = "esc({$context})";
                }
            }

            return $this->applyFilters($replace, $filters);
        }
        
        //--------------------------------------------------------------------

        /**
         * Checks the placeholder the view provided to see if we need to provide any autoescaping.
         *
         * @param string $key
         *
         * @return false|string
         */
        public function shouldAddEscaping(string $key)
        {
            $escape = false;

            $key = trim(str_replace(['{', '}'], '', $key));

            // If the key has a context stored (from setData)
            // we need to respect that.
            if (array_key_exists($key, $this->dataContexts))
            {
                if ($this->dataContexts[$key] !== 'raw')
                {
                    return $this->dataContexts[$key];
                }
            }
            // No pipes, then we know we need to escape
            elseif (strpos($key, '|') === false)
            {
                $escape = 'html';
            }
            // If there's a `noescape` then we're definitely false.
            elseif (strpos($key, 'noescape') !== false)
            {
                $escape = false;
            }
            // If no `esc` filter is found, then we'll need to add one.
            elseif (! preg_match('/\s+esc/', $key))
            {
                $escape = 'html';
            }

            return $escape;
        }
        
        //--------------------------------------------------------------------

        /**
         * Given a set of filters, will apply each of the filters in turn
         * to $replace, and return the modified string.
         *
         * @param string $replace
         * @param array  $filters
         *
         * @return string
         */
        protected function applyFilters(string $replace, array $filters): string
        {
            // Determine the requested filters
            foreach ($filters as $filter)
            {
                // Grab any parameter we might need to send
                preg_match('/\([\w<>=\/\\\,:.\-\s\+]+\)/', $filter, $param);

                // Remove the () and spaces to we have just the parameter left
                $param = ! empty($param) ? trim($param[0], '() ') : null;

                // Params can be separated by commas to allow multiple parameters for the filter
                if (! empty($param))
                {
                    $param = explode(',', $param);

                    // Clean it up
                    foreach ($param as &$p)
                    {
                        $p = trim($p, ' "');
                    }
                }
                else
                {
                    $param = [];
                }

                // Get our filter name
                $filter = ! empty($param) ? trim(strtolower(substr($filter, 0, strpos($filter, '(')))) : trim($filter);

                if (! array_key_exists($filter, $this->filters))
                {
                    continue;
                }

                // Filter it....
                $replace = $this->config->filters[$filter]($replace, ...$param);
            }

            return $replace;
        }


        //--------------------------------------------------------------------

        /**
         * Re-inserts the noparsed contents back into the template.
         *
         * @param string $template
         *
         * @return string
         */
        public function insertNoparse(string $template): string
        {
            foreach ($this->noparseBlocks as $hash => $replace)
            {
                $template = str_replace("noparse_{$hash}", $replace, $template);
                unset($this->noparseBlocks[$hash]);
            }

            return $template;
        }

        /**
         * Parses any conditionals in the code, removing blocks that don't
         * pass so we don't try to parse it later.
         *
         * Valid conditionals:
         *  - if
         *  - elseif
         *  - else
         *
         * @param string $template
         *
         * @return string
         */
        protected function parseConditionals(string $template,array $data): string
        {
            $pattern = '/\{\s*(if|elseif)\s*((?:\()?(.*?)(?:\))?)\s*\}/ms';

            /**
             * For each match:
             * [0] = raw match `{if var}`
             * [1] = conditional `if`
             * [2] = condition `do === true`
             * [3] = same as [2]
             */
            preg_match_all($pattern, $template, $matches, PREG_SET_ORDER);

            foreach ($matches as $match)
            {
                // Build the string to replace the `if` statement with.
                $condition = $match[2];

                $statement = $match[1] === 'elseif' ? '<?php elseif (' . $condition . '): ?>' : '<?php if (' . $condition . '): ?>';
                $template  = str_replace($match[0], $statement, $template);
            }

            $template = preg_replace('/\{\s*else\s*\}/ms', '<?php else: ?>', $template);
            $template = preg_replace('/\{\s*endif\s*\}/ms', '<?php endif; ?>', $template);

            // Parse the PHP itself, or insert an error so they can debug
            ob_start();

            if (is_null($this->tempData))
            {
                $this->tempData = $this->data;
            }

            extract($this->tempData);

            try
            {
                eval('?>' . $template . '<?php ');
            }
            catch (\ParseError $e)
            {
                ob_end_clean();
                throw ViewException::forTagSyntaxError(str_replace(['?>', '<?php '], '', $template));
            }

            return ob_get_clean();
        }
            

    }

?>