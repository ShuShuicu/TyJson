<?php

/**
 * TyJson API 处理类
 */

require_once __DIR__ . '/DB.php';

class TyJson_Action extends Typecho_Widget
{
    private $dbApi;
    private $contentFormat = 'html';
    private $allowedFormats = ['html', 'markdown'];

    // HTTP 状态码常量
    const HTTP_OK = 200;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_INTERNAL_ERROR = 500;

    // 分页设置
    const DEFAULT_PAGE_SIZE = 10;
    const MAX_PAGE_SIZE = 100;
    const DEFAULT_PAGE = 1;

    public function __construct($request, $response, $params = null)
    {
        parent::__construct($request, $response, $params);
        $this->dbApi = new TyJson_Db();
    }

    /**
     * 路由分发入口
     */
    public function dispatch()
    {
        try {
            $this->init();

            $path = $this->getRequestPath();
            $pathParts = explode('/', trim($path, '/'));

            // 路由分发
            switch ($pathParts[0]) {
                case 'site':
                    $response = $this->handleIndex();
                    break;
                case 'posts':
                    $response = $this->handlePostList($pathParts);
                    break;
                case 'content':
                    $response = $this->handlePostContent($pathParts);
                    break;
                case 'category':
                    $response = $this->handleCategory($pathParts);
                    break;
                case 'tag':
                    $response = $this->handleTag($pathParts);
                    break;
                case 'search':
                    $response = $this->handleSearch($pathParts);
                    break;
                case 'options':
                    $response = $this->handleOptions($pathParts);
                    break;
                case 'themeOptions':
                    $response = $this->handleThemeOptions($pathParts);
                    break;
                case 'fields':
                    $response = $this->handleFieldSearch($pathParts);
                    break;
                case 'advancedFields':
                    $response = $this->handleAdvancedFieldSearch($pathParts);
                    break;
                case 'comments':
                    $response = $this->handleComments($pathParts);
                    break;
                case 'pages':
                    $response = $this->handlePageList($pathParts);
                    break;
                case 'attachments':
                    $response = $this->handleAttachmentList($pathParts);
                    break;
                default:
                    $this->sendErrorResponse('Not Found', self::HTTP_NOT_FOUND);
            }

            $this->sendResponse($response);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), self::HTTP_INTERNAL_ERROR, $e);
        }
    }

    /**
     * 初始化API设置
     */
    protected function init()
    {
        // 检查请求方法
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendErrorResponse('Method Not Allowed', self::HTTP_METHOD_NOT_ALLOWED);
        }

        // 设置响应头 - 使用 setHeader() 替代 addHeader()
        $this->response->setContentType('application/json');
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');
        $this->response->setHeader('Access-Control-Allow-Origin', '*');

        // 设置内容格式
        $this->contentFormat = $this->getRequestFormat();
    }

    /**
     * 获取请求路径
     */
    private function getRequestPath()
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = '/ty-json/';
        
        // 确保正确处理带查询参数的URL
        if (strpos($requestUri, '?') !== false) {
            $requestUri = strstr($requestUri, '?', true);
        }
        
        if (strpos($requestUri, $basePath) === 0) {
            $path = substr($requestUri, strlen($basePath));
            return $path === false ? '/' : $path;
        }
        
        return '/';
    }

    /**
     * 获取请求的内容格式
     */
    private function getRequestFormat()
    {
        if (isset($_GET['format']) && in_array(strtolower($_GET['format']), $this->allowedFormats, true)) {
            return strtolower($_GET['format']);
        }
        return 'html';
    }

    /**
     * 获取分页大小
     */
    private function getPageSize()
    {
        $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : self::DEFAULT_PAGE_SIZE;
        return min(max(1, $pageSize), self::MAX_PAGE_SIZE);
    }

    /**
     * 获取当前页码
     */
    private function getCurrentPage()
    {
        return max(1, (int)($_GET['page'] ?? self::DEFAULT_PAGE));
    }

    /**
     * 发送JSON响应
     */
    private function sendResponse(array $response, $statusCode = self::HTTP_OK)
    {
        $response = array_merge([
            'code' => $statusCode,
            'message' => 'success',
            'data' => null,
            'meta' => [
                'format' => $this->contentFormat,
                'timestamp' => time()
            ]
        ], $response);

        $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if (defined('__DEBUG__') && __DEBUG__) {
            $options |= JSON_PRETTY_PRINT;
        }

        $this->response->setStatus($statusCode);
        echo json_encode($response, $options);
        exit;
    }

    /**
     * 发送错误响应
     */
    private function sendErrorResponse($message, $code, Exception $e = null)
    {
        $response = [
            'code' => $code,
            'message' => $message,
            'timestamp' => time()
        ];

        if ($e && defined('__DEBUG__') && __DEBUG__) {
            $response['error'] = $e->getMessage();
            $response['trace'] = $e->getTraceAsString();
        }

        $this->sendResponse($response, $code);
    }

    /**
     * 处理首页请求
     */
    private function handleIndex()
    {
        return [
            'data' => [
                'site' => $this->getSiteInfo(),
                'version' => [
                    'typecho' => Typecho_Common::VERSION,
                    'php' => phpversion(),
                    'theme' => $this->getThemeInfo()
                ],
            ]
        ];
    }

    /**
     * 获取站点信息
     */
    private function getSiteInfo()
    {
        $options = Helper::options();
        return [
            'theme' => $options->theme,
            'title' => $options->title,
            'description' => $this->formatContent($options->description),
            'keywords' => $options->keywords,
            'siteUrl' => $options->siteUrl,
            'timezone' => $options->timezone,
            'lang' => $options->lang ?: 'zh-CN',
        ];
    }

    /**
     * 获取主题信息
     */
    private function getThemeInfo()
    {
        $db = Typecho_Db::get();
        $row = $db->fetchRow($db->select('value')
            ->from('table.options')
            ->where('name = ?', 'theme:' . Helper::options()->theme)
            ->limit(1));

        if ($row && isset($row['value'])) {
            $themeOptions = unserialize($row['value']);
            return $themeOptions['version'] ?? '1.0.0';
        }

        return '1.0.0';
    }

    /**
     * 处理文章列表请求
     */
    private function handlePostList($pathParts)
    {
        $pageSize = $this->getPageSize();
        $currentPage = $this->getCurrentPage();

        $posts = $this->dbApi->getPostList($pageSize, $currentPage);
        $total = $this->dbApi->getTotalPosts();

        return [
            'data' => [
                'list' => array_map([$this, 'formatPost'], $posts),
                'pagination' => $this->buildPagination($total, $pageSize, $currentPage, 'posts'),
                'page' => $currentPage,
                'pageSize' => $pageSize,
                'total' => $total
            ]
        ];
    }

    /**
     * 处理文章内容请求
     */
    private function handlePostContent($pathParts)
    {
        if (count($pathParts) < 2) {
            $this->sendErrorResponse('Missing post identifier', self::HTTP_BAD_REQUEST);
        }

        $identifier = $pathParts[1];
        $isCid = is_numeric($identifier);

        $post = $isCid ? $this->dbApi->getPostDetail($identifier) : $this->dbApi->getPostDetailBySlug($identifier);

        if (!$post) {
            $this->sendErrorResponse('Post not found', self::HTTP_NOT_FOUND);
        }

        return [
            'data' => $this->formatPost($post, true),
            'page' => 1,
            'pageSize' => 1,
            'total' => 1
        ];
    }

    /**
     * 处理分类请求
     */
    private function handleCategory($pathParts)
    {
        if (count($pathParts) === 1) {
            // 获取所有分类
            return [
                'data' => array_map([$this, 'formatCategory'], $this->dbApi->getAllCategories()),
                'page' => 1,
                'pageSize' => 'all',
                'total' => count($this->dbApi->getAllCategories())
            ];
        }

        // 获取特定分类下的文章
        $identifier = $pathParts[1];
        $isMid = is_numeric($identifier);

        $category = $isMid ? $this->dbApi->getCategoryByMid($identifier) : $this->dbApi->getCategoryBySlug($identifier);

        if (!$category) {
            $this->sendErrorResponse('Category not found', self::HTTP_NOT_FOUND);
        }

        $pageSize = $this->getPageSize();
        $currentPage = $this->getCurrentPage();

        $posts = $this->dbApi->getPostsInCategory($category['mid'], $pageSize, $currentPage);
        $total = $this->dbApi->getTotalPostsInCategory($category['mid']);

        return [
            'data' => [
                'category' => $this->formatCategory($category),
                'list' => array_map([$this, 'formatPost'], $posts),
                'pagination' => $this->buildPagination($total, $pageSize, $currentPage, 'category'),
                'page' => $currentPage,
                'pageSize' => $pageSize,
                'total' => $total
            ]
        ];
    }

    /**
     * 处理标签请求
     */
    private function handleTag($pathParts)
    {
        if (count($pathParts) === 1) {
            // 获取所有标签
            return [
                'data' => array_map([$this, 'formatTag'], $this->dbApi->getAllTags()),
                'page' => 1,
                'pageSize' => 'all',
                'total' => count($this->dbApi->getAllTags())
            ];
        }

        // 获取特定标签下的文章
        $identifier = $pathParts[1];
        $isMid = is_numeric($identifier);

        $tag = $isMid ? $this->dbApi->getTagByMid($identifier) : $this->dbApi->getTagBySlug($identifier);

        if (!$tag) {
            $this->sendErrorResponse('Tag not found', self::HTTP_NOT_FOUND);
        }

        $pageSize = $this->getPageSize();
        $currentPage = $this->getCurrentPage();

        $posts = $this->dbApi->getPostsInTag($tag['mid'], $pageSize, $currentPage);
        $total = $this->dbApi->getTotalPostsInTag($tag['mid']);

        return [
            'data' => [
                'tag' => $this->formatTag($tag),
                'list' => array_map([$this, 'formatPost'], $posts),
                'pagination' => $this->buildPagination($total, $pageSize, $currentPage, 'tag'),
                'page' => $currentPage,
                'pageSize' => $pageSize,
                'total' => $total
            ]
        ];
    }

    /**
     * 处理搜索请求
     */
    private function handleSearch($pathParts)
    {
        if (count($pathParts) < 2 || empty($pathParts[1])) {
            $this->sendErrorResponse('Missing search keyword', self::HTTP_BAD_REQUEST);
        }

        $keyword = urldecode($pathParts[1]);
        $pageSize = $this->getPageSize();
        $currentPage = $this->getCurrentPage();

        $posts = $this->dbApi->searchPosts($keyword, $pageSize, $currentPage);
        $total = $this->dbApi->getSearchPostsCount($keyword);

        return [
            'data' => [
                'keyword' => $keyword,
                'list' => array_map([$this, 'formatPost'], $posts),
                'pagination' => $this->buildPagination($total, $pageSize, $currentPage, 'search'),
                'page' => $currentPage,
                'pageSize' => $pageSize,
                'total' => $total
            ]
        ];
    }

    /**
     * 处理options请求
     */
    private function handleOptions($pathParts)
    {
        if (count($pathParts) === 1) {
            // 获取所有公开选项
            return [
                'data' => $this->getAllPublicOptions(),
                'page' => 1,
                'pageSize' => 'all',
                'total' => count($this->getAllPublicOptions())
            ];
        }

        // 获取特定选项
        $optionName = $pathParts[1];
        $optionValue = Helper::options()->{$optionName};

        if ($optionValue === null) {
            $this->sendErrorResponse('Option not found', self::HTTP_NOT_FOUND);
        }

        return [
            'data' => [
                'name' => $optionName,
                'value' => $optionValue
            ],
            'page' => 1,
            'pageSize' => 1,
            'total' => 1
        ];
    }

    /**
     * 获取所有公开选项
     */
    private function getAllPublicOptions()
    {
        $options = Helper::options();
        $publicOptions = [];

        $allowedOptions = [
            'title',
            'description',
            'keywords',
            'theme',
            'plugins',
            'timezone',
            'lang',
            'charset',
            'contentType',
            'siteUrl',
            'rootUrl',
            'rewrite',
            'generator',
            'feedUrl',
            'searchUrl'
        ];

        foreach ($allowedOptions as $option) {
            if (isset($options->{$option})) {
                $publicOptions[$option] = $options->{$option};
            }
        }

        return $publicOptions;
    }

    /**
     * 处理主题设置请求
     */
    private function handleThemeOptions($pathParts)
    {
        $themeName = Helper::options()->theme;
        $themeOptions = $this->getThemeOptions($themeName);

        if (count($pathParts) === 1) {
            // 获取所有主题设置
            return [
                'data' => $themeOptions,
                'page' => 1,
                'pageSize' => 'all',
                'total' => count($themeOptions)
            ];
        }

        // 获取特定主题设置项
        $optionName = $pathParts[1];

        if (!isset($themeOptions[$optionName])) {
            $this->sendErrorResponse('Theme option not found', self::HTTP_NOT_FOUND);
        }

        return [
            'data' => [
                'name' => $optionName,
                'value' => $themeOptions[$optionName]
            ],
            'page' => 1,
            'pageSize' => 1,
            'total' => 1
        ];
    }

    /**
     * 获取主题设置项
     */
    private function getThemeOptions($themeName)
    {
        $db = Typecho_Db::get();
        $row = $db->fetchRow($db->select('value')
            ->from('table.options')
            ->where('name = ?', 'theme:' . $themeName)
            ->limit(1));

        if (!$row || !isset($row['value'])) {
            return [];
        }

        $options = @unserialize($row['value']);
        return is_array($options) ? $options : [];
    }

    /**
     * 处理字段搜索请求
     */
    private function handleFieldSearch($pathParts)
    {
        if (count($pathParts) < 3) {
            $this->sendErrorResponse('Missing field parameters', self::HTTP_BAD_REQUEST);
        }

        $fieldName = $pathParts[1];
        $fieldValue = urldecode($pathParts[2]);

        $pageSize = $this->getPageSize();
        $currentPage = $this->getCurrentPage();

        $posts = $this->dbApi->getPostsByField($fieldName, $fieldValue, $pageSize, $currentPage);
        $total = $this->dbApi->getPostsCountByField($fieldName, $fieldValue);

        return [
            'data' => [
                'conditions' => [
                    'name' => $fieldName,
                    'value' => $fieldValue,
                    'value_type' => $_GET['value_type'] ?? 'str'
                ],
                'list' => array_map([$this, 'formatPost'], $posts),
                'pagination' => $this->buildPagination($total, $pageSize, $currentPage, 'field'),
                'page' => $currentPage,
                'pageSize' => $pageSize,
                'total' => $total
            ]
        ];
    }

    /**
     * 处理高级字段搜索请求
     */
    private function handleAdvancedFieldSearch($pathParts)
    {
        $conditions = [];

        // 精简匹配 {name}/{value}
        if (count($pathParts) >= 3) {
            $fieldName = $pathParts[1];
            $fieldValue = urldecode($pathParts[2]);

            $conditions[] = [
                'name' => $fieldName,
                'operator' => $_GET['operator'] ?? '=',
                'value' => $fieldValue,
                'value_type' => $_GET['value_type'] ?? 'str'
            ];
        }
        // 高级字段 ?conditions=[JSON]
        elseif (isset($_GET['conditions'])) {
            $decoded = json_decode($_GET['conditions'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $conditions = $decoded;
            }
        }

        if (empty($conditions)) {
            $this->sendErrorResponse('Invalid search conditions', self::HTTP_BAD_REQUEST);
        }

        $pageSize = $this->getPageSize();
        $currentPage = $this->getCurrentPage();

        $posts = $this->dbApi->getPostsByAdvancedFields($conditions, $pageSize, $currentPage);
        $total = $this->dbApi->getPostsCountByAdvancedFields($conditions);

        return [
            'data' => [
                'conditions' => $conditions,
                'list' => array_map([$this, 'formatPost'], $posts),
                'pagination' => $this->buildPagination($total, $pageSize, $currentPage, 'advanced-fields'),
                'page' => $currentPage,
                'pageSize' => $pageSize,
                'total' => $total
            ]
        ];
    }

    /**
     * 处理评论请求
     */
    private function handleComments($pathParts)
    {
        if (count($pathParts) === 1) {
            // 获取所有评论
            $pageSize = $this->getPageSize();
            $currentPage = $this->getCurrentPage();

            $comments = $this->dbApi->getAllComments($pageSize, $currentPage);
            $total = $this->dbApi->getTotalComments();

            return [
                'data' => [
                    'list' => array_map([$this, 'formatComment'], $comments),
                    'pagination' => $this->buildPagination($total, $pageSize, $currentPage, 'comments'),
                    'page' => $currentPage,
                    'pageSize' => $pageSize,
                    'total' => $total
                ]
            ];
        } elseif (count($pathParts) >= 2 && $pathParts[1] === 'post') {
            // 获取特定文章的评论
            if (count($pathParts) < 3) {
                $this->sendErrorResponse('Missing post ID', self::HTTP_BAD_REQUEST);
            }

            $cid = $pathParts[2];
            if (!is_numeric($cid)) {
                $this->sendErrorResponse('Invalid post ID', self::HTTP_BAD_REQUEST);
            }

            $pageSize = $this->getPageSize();
            $currentPage = $this->getCurrentPage();

            $comments = $this->dbApi->getPostComments($cid, $pageSize, $currentPage);
            $total = $this->dbApi->getTotalPostComments($cid);

            // 检查文章是否存在
            $post = $this->dbApi->getPostDetail($cid);
            if (!$post) {
                $this->sendErrorResponse('Post not found', self::HTTP_NOT_FOUND);
            }

            return [
                'data' => [
                    'post' => [
                        'cid' => (int)$post['cid'],
                        'title' => $post['title'] ?? ''
                    ],
                    'list' => array_map([$this, 'formatComment'], $comments),
                    'pagination' => $this->buildPagination($total, $pageSize, $currentPage, 'comments'),
                    'page' => $currentPage,
                    'pageSize' => $pageSize,
                    'total' => $total
                ]
            ];
        }

        $this->sendErrorResponse('Not Found', self::HTTP_NOT_FOUND);
    }

    /**
     * 处理页面列表请求
     */
    private function handlePageList($pathParts)
    {
        $pageSize = $this->getPageSize();
        $currentPage = $this->getCurrentPage();

        $pages = $this->dbApi->getAllPages($pageSize, $currentPage);
        $total = $this->dbApi->getTotalPages();

        return [
            'data' => [
                'list' => array_map([$this, 'formatPost'], $pages),
                'pagination' => $this->buildPagination($total, $pageSize, $currentPage, 'pages'),
                'page' => $currentPage,
                'pageSize' => $pageSize,
                'total' => $total
            ]
        ];
    }

    /**
     * 处理附件列表请求
     */
    private function handleAttachmentList($pathParts)
    {
        $pageSize = $this->getPageSize();
        $currentPage = $this->getCurrentPage();

        $attachments = $this->dbApi->getAllAttachments($pageSize, $currentPage);
        $total = $this->dbApi->getTotalAttachments();

        return [
            'data' => [
                'list' => array_map([$this, 'formatAttachment'], $attachments),
                'pagination' => $this->buildPagination($total, $pageSize, $currentPage, 'attachments'),
                'page' => $currentPage,
                'pageSize' => $pageSize,
                'total' => $total
            ]
        ];
    }

    /**
     * 构建分页数据
     */
    private function buildPagination($total, $pageSize, $currentPage, $type = null)
    {
        $pagination = [
            'total' => (int)$total,
            'pageSize' => $pageSize,
            'currentPage' => $currentPage,
            'totalPages' => max(1, ceil($total / $pageSize))
        ];

        if ($type) {
            $pagination['type'] = $type;
        }

        return $pagination;
    }

    /**
     * 格式化分类数据
     */
    private function formatCategory($category)
    {
        if (!is_array($category)) {
            return $category;
        }

        $category['description'] = $this->formatContent($category['description'] ?? '');
        return $category;
    }

    /**
     * 格式化标签数据
     */
    private function formatTag($tag)
    {
        if (!is_array($tag)) {
            return $tag;
        }

        $tag['description'] = $this->formatContent($tag['description'] ?? '');
        return $tag;
    }

    /**
     * 格式化文章数据
     */
    private function formatPost($post, $includeContent = false)
    {
        if (!is_array($post)) {
            return $post;
        }

        $formattedPost = [
            'cid' => (int)$post['cid'],
            'title' => $post['title'] ?? '',
            'slug' => $post['slug'] ?? '',
            'type' => $post['type'] ?? 'post',
            'created' => date('c', $post['created'] ?? time()),
            'modified' => date('c', $post['modified'] ?? time()),
            'commentsNum' => (int)($post['commentsNum'] ?? 0),
            'authorId' => (int)($post['authorId'] ?? 0),
            'status' => $post['status'] ?? 'publish',
            'contentType' => $this->contentFormat,
            'fields' => $this->dbApi->getPostFields($post['cid'] ?? 0),
        ];

        if ($formattedPost['type'] === 'post') {
            $formattedPost['categories'] = array_map(
                [$this, 'formatCategory'],
                $this->dbApi->getPostCategories($post['cid'] ?? 0)
            );
            $formattedPost['tags'] = array_map(
                [$this, 'formatTag'],
                $this->dbApi->getPostTags($post['cid'] ?? 0)
            );
        }

        $excerptLength = isset($_GET['excerptLength']) ? (int)$_GET['excerptLength'] : 200;
        $formattedPost['content'] = $this->formatContent($post['text'] ?? '');
        $formattedPost['excerpt'] = $this->generatePlainExcerpt($post['text'] ?? '', $excerptLength);

        return $formattedPost;
    }

    /**
     * 格式化评论数据
     */
    private function formatComment($comment)
    {
        if (!is_array($comment)) {
            return $comment;
        }

        return [
            'coid' => (int)$comment['coid'],
            'cid' => (int)$comment['cid'],
            'author' => $comment['author'] ?? '',
            'mail' => $comment['mail'] ?? '',
            'url' => $comment['url'] ?? '',
            'ip' => $comment['ip'] ?? '',
            'created' => date('c', $comment['created'] ?? time()),
            'modified' => date('c', $comment['modified'] ?? time()),
            'text' => $this->formatContent($comment['text'] ?? ''),
            'status' => $comment['status'] ?? 'approved',
            'parent' => (int)($comment['parent'] ?? 0),
            'authorId' => (int)($comment['authorId'] ?? 0)
        ];
    }

    /**
     * 格式化附件数据
     */
    private function formatAttachment($attachment)
    {
        if (!is_array($attachment)) {
            return $attachment;
        }

        $options = Helper::options();

        return [
            'cid' => (int)$attachment['cid'],
            'title' => $attachment['title'] ?? '',
            'type' => $attachment['type'] ?? '',
            'size' => (int)($attachment['size'] ?? 0),
            'created' => date('c', $attachment['created'] ?? time()),
            'modified' => date('c', $attachment['modified'] ?? time()),
            'status' => $attachment['status'] ?? 'publish',
        ];
    }

    /**
     * 格式化内容为指定格式
     */
    private function formatContent($content)
    {
        if ($this->contentFormat === 'markdown') {
            return $content;
        }

        if (!class_exists('Markdown')) {
            require_once __TYPECHO_ROOT_DIR__ . '/var/Typecho/Common/Markdown.php';
        }

        $content = preg_replace('/<!--.*?-->/s', '', $content);
        return Markdown::convert($content);
    }

    /**
     * 生成纯文本摘要
     */
    private function generatePlainExcerpt($content, $length = 200)
    {
        $text = strip_tags($content);
        $text = preg_replace('/```.*?```/s', '', $text);
        $text = preg_replace('/~~~.*?~~~/s', '', $text);
        $text = preg_replace('/`.*?`/', '', $text);
        $text = preg_replace('/\$\$([^\$\$]+)\]\$[^)]+\$/', '$1', $text);
        $text = preg_replace('/!\$\$([^\$\$]*)\]\$[^)]+\$/', '', $text);
        $text = preg_replace('/^#{1,6}\s*/m', '', $text);
        $text = preg_replace('/[\*\_]{1,3}([^*_]+)[\*\_]{1,3}/', '$1', $text);
        $text = preg_replace('/^\s*>\s*/m', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (mb_strlen($text) > $length) {
            $text = mb_substr($text, 0, $length);
            if (preg_match('/\s\S+$/', $text, $matches)) {
                $text = mb_substr($text, 0, mb_strlen($text) - mb_strlen($matches[0]));
            }
        }

        return $text;
    }
}
