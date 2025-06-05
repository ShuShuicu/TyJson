<?php

/**
 * TyJson 数据库操作类
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
class TyJson_Db
{
    private $db;

    public function __construct()
    {
        $this->db = Typecho_Db::get();
    }

    /**
     * 获取文章列表
     */
    public function getPostList($pageSize = 10, $currentPage = 1)
    {
        $query = $this->db->select()->from('table.contents')
            ->where('status = ?', 'publish')
            ->where('type = ?', 'post')
            ->order('created', Typecho_Db::SORT_DESC)
            ->page($currentPage, $pageSize);

        return $this->db->fetchAll($query);
    }

    /**
     * 获取文章详情
     */
    public function getPostDetail($cid)
    {
        return $this->db->fetchRow($this->db->select()->from('table.contents')->where('cid = ?', $cid)->limit(1));
    }

    /**
     * 通过slug获取文章详情
     */
    public function getPostDetailBySlug($slug)
    {
        return $this->db->fetchRow($this->db->select()->from('table.contents')->where('slug = ?', $slug)->limit(1));
    }

    /**
     * 获取总文章数
     */
    public function getTotalPosts()
    {
        $rs = $this->db->fetchRow($this->db->select('COUNT(*)')
            ->from('table.contents')
            ->where('status = ?', 'publish')
            ->where('type = ?', 'post'));
        return $rs['COUNT(*)'] ?? 0;
    }

    /**
     * 获取分类下的文章列表
     * @param int $cid 分类ID
     * @return array
     */
    public function getPostsInCategory($mid, $pageSize = 10, $currentPage = 1)
    {
        $query = $this->db->select()->from('table.contents')
            ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
            ->where('table.relationships.mid = ?', $mid)
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.type = ?', 'post')
            ->order('table.contents.created', Typecho_Db::SORT_DESC)
            ->page($currentPage, $pageSize);

        return $this->db->fetchAll($query);
    }

    /**
     * 获取分类下的文章总数
     * @param int $cid 分类ID
     * @return int
     */
    public function getTotalPostsInCategory($cid)
    {
        $rs = $this->db->fetchRow($this->db->select('COUNT(*)')
            ->from('table.contents')
            ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
            ->where('table.relationships.mid = ?', $cid)
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.type = ?', 'post'));
        return $rs['COUNT(*)'] ?? 0;
    }

    /**
     * 获取标签下的文章列表
     * @param int $tid 标签ID
     * @return array
     */
    public function getPostsInTag($mid, $pageSize = 10, $currentPage = 1)
    {
        $query = $this->db->select()->from('table.contents')
            ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
            ->where('table.relationships.mid = ?', $mid)
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.type = ?', 'post')
            ->order('table.contents.created', Typecho_Db::SORT_DESC)
            ->page($currentPage, $pageSize);

        return $this->db->fetchAll($query);
    }

    /**
     * 获取标签下的文章总数
     * @param int $tid 标签ID
     * @return int
     */
    public function getTotalPostsInTag($tid)
    {
        $rs = $this->db->fetchRow($this->db->select('COUNT(*)')
            ->from('table.contents')
            ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
            ->where('table.relationships.mid = ?', $tid)
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.type = ?', 'post'));
        return $rs['COUNT(*)'] ?? 0;
    }

    /**
     * 获取所有分类
     * @return array
     */
    public function getAllCategories()
    {
        $query = $this->db->select()->from('table.metas')
            ->where('type = ?', 'category')
            ->order('order', Typecho_Db::SORT_ASC);

        return $this->db->fetchAll($query);
    }
    /**
     * 通过slug查询分类详情
     * @param string $slug slug
     * @return array
     */
    public function getCategoryBySlug($slug)
    {
        return $this->db->fetchRow($this->db->select()->from('table.metas')
            ->where('slug = ?', $slug)
            ->where('type = ?', 'category')
            ->limit(1));
    }
    /**
     * 通过mid查询分类详情
     * @param int $mid mid
     * @return array
     */
    public function getCategoryByMid($mid)
    {
        return $this->db->fetchRow($this->db->select()->from('table.metas')
            ->where('mid = ?', $mid)
            ->where('type = ?', 'category')
            ->limit(1));
    }
    /**
     * 获取所有标签
     * @return array
     */
    public function getAllTags()
    {
        $query = $this->db->select()->from('table.metas')
            ->where('type = ?', 'tag')
            ->order('count', Typecho_Db::SORT_DESC);

        return $this->db->fetchAll($query);
    }
    /**
     * 通过slug查询标签详情
     * @param string $slug slug
     * @return array
     */
    public function getTagBySlug($slug)
    {
        return $this->db->fetchRow($this->db->select()->from('table.metas')
            ->where('slug = ?', $slug)
            ->where('type = ?', 'tag')
            ->limit(1));
    }
    /**
     * 通过mid查询标签详情
     * @param int $mid mid
     * @return array
     */
    public function getTagByMid($mid)
    {
        return $this->db->fetchRow($this->db->select()->from('table.metas')
            ->where('mid = ?', $mid)
            ->where('type = ?', 'tag')
            ->limit(1));
    }

    /**
     * 获取文章分类
     * @param int $cid 文章ID
     * @return array
     */
    public function getPostCategories($cid)
    {
        $query = $this->db->select()->from('table.metas')
            ->join('table.relationships', 'table.metas.mid = table.relationships.mid')
            ->where('table.relationships.cid = ?', $cid)
            ->where('table.metas.type = ?', 'category');

        return $this->db->fetchAll($query);
    }

    /**
     * 获取文章标签
     * @param int $cid 文章ID
     * @return array
     */
    public function getPostTags($cid)
    {
        $query = $this->db->select()->from('table.metas')
            ->join('table.relationships', 'table.metas.mid = table.relationships.mid')
            ->where('table.relationships.cid = ?', $cid)
            ->where('table.metas.type = ?', 'tag');

        return $this->db->fetchAll($query);
    }

    /**
     * 获取文章的自定义字段
     * @param int $cid 文章ID
     * @return array
     */
    public function getPostFields($cid)
    {
        $query = $this->db->select()->from('table.fields')
            ->where('cid = ?', $cid);

        $fields = $this->db->fetchAll($query);

        $result = [];
        foreach ($fields as $field) {
            // 根据字段类型获取对应的值
            $valueField = $field['type'] . '_value';
            $result[$field['name']] = $field[$valueField] ?? null;
        }

        return $result;
    }

    /**
     * 根据字段值获取文章
     * @param string $fieldName 字段名
     * @param mixed $fieldValue 字段值
     * @param int $pageSize 每页数量
     * @param int $currentPage 当前页码
     * @return array
     */
    public function getPostsByField($fieldName, $fieldValue, $pageSize, $currentPage)
    {
        $query = $this->db->select('table.contents.*')
            ->from('table.contents')
            ->join('table.fields', 'table.contents.cid = table.fields.cid')
            ->where('table.fields.name = ?', $fieldName)
            ->where('table.fields.str_value = ?', $fieldValue)
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.type = ?', 'post')
            ->order('table.contents.created', Typecho_Db::SORT_DESC)
            ->page($currentPage, $pageSize);

        return $this->db->fetchAll($query);
    }

    /**
     * 获取符合字段条件的文章总数
     * @param string $fieldName 字段名
     * @param mixed $fieldValue 字段值
     * @return int
     */
    public function getPostsCountByField($fieldName, $fieldValue)
    {
        $rs = $this->db->fetchRow($this->db->select('COUNT(*)')
            ->from('table.contents')
            ->join('table.fields', 'table.contents.cid = table.fields.cid')
            ->where('table.fields.name = ?', $fieldName)
            ->where('table.fields.str_value = ?', $fieldValue)
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.type = ?', 'post'));

        return (int) ($rs['COUNT(*)'] ?? 0);
    }

    /**
     * 高级字段查询方法
     * @param array $conditions 查询条件数组
     * @param int $pageSize 每页数量
     * @param int $currentPage 当前页码
     * @return array
     */
    public function getPostsByAdvancedFields($conditions, $pageSize, $currentPage)
    {
        $query = $this->db->select('DISTINCT table.contents.*')
            ->from('table.contents')
            ->join('table.fields', 'table.contents.cid = table.fields.cid');

        // 处理多个字段条件
        foreach ($conditions as $condition) {
            $fieldName = $condition['name'] ?? '';
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? '';
            $valueType = $condition['value_type'] ?? 'str';

            // 验证操作符
            $validOperators = ['=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'];
            if (!in_array($operator, $validOperators)) {
                continue;
            }

            // 构建条件
            $valueField = $valueType . '_value';
            $where = "table.fields.name = ? AND table.fields.{$valueField} {$operator} ?";

            // 处理IN/NOT IN操作符
            if (in_array($operator, ['IN', 'NOT IN'])) {
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $where = "table.fields.name = ? AND table.fields.{$valueField} {$operator} ({$placeholders})";
            }

            $query->where($where, $fieldName, ...(array)$value);
        }

        // 基本条件
        $query->where('table.contents.status = ?', 'publish')
            ->where('table.contents.type = ?', 'post')
            ->order('table.contents.created', Typecho_Db::SORT_DESC)
            ->page($currentPage, $pageSize);

        return $this->db->fetchAll($query);
    }

    /**
     * 获取高级字段查询的文章总数
     * @param array $conditions 查询条件数组
     * @return int
     */
    public function getPostsCountByAdvancedFields($conditions)
    {
        $query = $this->db->select('COUNT(DISTINCT table.contents.cid)')
            ->from('table.contents')
            ->join('table.fields', 'table.contents.cid = table.fields.cid');

        // 处理多个字段条件
        foreach ($conditions as $condition) {
            $fieldName = $condition['name'] ?? '';
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? '';
            $valueType = $condition['value_type'] ?? 'str';

            $validOperators = ['=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'];
            if (!in_array($operator, $validOperators)) {
                continue;
            }

            $valueField = $valueType . '_value';
            $where = "table.fields.name = ? AND table.fields.{$valueField} {$operator} ?";

            if (in_array($operator, ['IN', 'NOT IN'])) {
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $where = "table.fields.name = ? AND table.fields.{$valueField} {$operator} ({$placeholders})";
            }

            $query->where($where, $fieldName, ...(array)$value);
        }

        $query->where('table.contents.status = ?', 'publish')
            ->where('table.contents.type = ?', 'post');

        $rs = $this->db->fetchRow($query);
        return (int) ($rs['COUNT(DISTINCT table.contents.cid)'] ?? 0);
    }

    /**
     * 搜索文章
     * @param string $keyword 搜索关键词
     * @param int $pageSize 每页数量
     * @param int $currentPage 当前页码
     * @return array
     */
    public function searchPosts($keyword, $pageSize, $currentPage)
    {
        try {
            // 使用 Typecho 的过滤方法处理搜索词
            $filteredKeyword = Typecho_Common::filterSearchQuery($keyword);
            $searchKeyword = '%' . str_replace(' ', '%', $filteredKeyword) . '%';

            $query = $this->db->select()->from('table.contents')
                ->where('status = ?', 'publish')
                ->where('type = ?', 'post')
                ->where(
                    '(title LIKE ? OR text LIKE ?)',
                    $searchKeyword,
                    $searchKeyword
                )
                ->order('created', Typecho_Db::SORT_DESC)
                ->page($currentPage, $pageSize);

            return $this->db->fetchAll($query);
        } catch (Exception $e) {
            error_log("Database search error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 获取搜索结果总数
     * @param string $keyword 搜索关键词
     * @return int
     */
    public function getSearchPostsCount($keyword)
    {
        try {
            // 使用 Typecho 的过滤方法处理搜索词
            $filteredKeyword = Typecho_Common::filterSearchQuery($keyword);
            $searchKeyword = '%' . str_replace(' ', '%', $filteredKeyword) . '%';

            $rs = $this->db->fetchRow($this->db->select('COUNT(*)')
                ->from('table.contents')
                ->where('status = ?', 'publish')
                ->where('type = ?', 'post')
                ->where(
                    '(title LIKE ? OR text LIKE ?)',
                    $searchKeyword,
                    $searchKeyword
                ));

            return (int) ($rs['COUNT(*)'] ?? 0);
        } catch (Exception $e) {
            error_log("Count search error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 获取所有评论列表
     * @param int $pageSize 每页数量
     * @param int $currentPage 当前页码
     * @return array
     */
    public function getAllComments($pageSize, $currentPage)
    {
        $query = $this->db->select()->from('table.comments')
            ->order('created', Typecho_Db::SORT_DESC)
            ->limit($pageSize, ($currentPage - 1) * $pageSize);

        return $this->db->fetchAll($query);
    }
    /**
     * 获取总评论数
     * @return int
     */
    public function getTotalComments()
    {
        $rs = $this->db->fetchRow($this->db->select('COUNT(*)')->from('table.comments'));
        return (int) ($rs['COUNT(*)'] ?? 0);
    }
    /**
     * 获取文章的评论
     * @param int $cid 文章ID
     * @param int $pageSize 每页数量
     * @param int $currentPage 当前页码
     * @return array
     */
    public function getPostComments($cid, $pageSize, $currentPage)
    {
        $query = $this->db->select()->from('table.comments')
            ->where('cid = ?', $cid)
            ->order('created', Typecho_Db::SORT_ASC)
            ->page($currentPage, $pageSize);

        return $this->db->fetchAll($query);
    }
    /**
     * 获取文章的评论总数
     * @param int $cid 文章ID
     * @return int
     */
    public function getTotalPostComments($cid)
    {
        $rs = $this->db->fetchRow($this->db->select('COUNT(*)')
            ->from('table.comments')
            ->where('cid = ?', $cid));
        return (int) ($rs['COUNT(*)'] ?? 0);
    }

    /**
     * 获取所有附件列表
     * @param int $pageSize 每页数量
     * @param int $currentPage 当前页码
     * @return array
     */
    public function getAllAttachments($pageSize, $currentPage)
    {
        $query = $this->db->select()->from('table.contents')
            ->where('type = ?', 'attachment')
            ->order('created', Typecho_Db::SORT_DESC)
            ->page($currentPage, $pageSize);

        return $this->db->fetchAll($query);
    }

    /**
     * 获取附件总数
     * @return int
     */
    public function getTotalAttachments()
    {
        $rs = $this->db->fetchRow($this->db->select('COUNT(*)')
            ->from('table.contents')
            ->where('type = ?', 'attachment'));
        return (int) ($rs['COUNT(*)'] ?? 0);
    }
}
