<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Article extends BaseModel{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @param 渴望加载
     * @param 存在急切加载以缓解N + 1查询问题。例如，考虑Book与之相关的模型Author。这种关系定义如下
     * @link https://laravel.com/docs/4.2/eloquent#eager-loading
     */
    public function category(){
        return $this->belongsTo(Category::class);
    }

    /**
     * @param array $data
     * @return bool|void
     * @author shidatuo
     * @description 保存文章
     */
    public function storeData($data){
        //>如果没有描述,就截取文章内容前100字作为描述
        if(!NotEstr($data['description'])){
            $description = preg_replace(array('/[~*>#-]*/', '/!?\[.*\]\(.*\)/', '/\[.*\]/'), '', $data['markdown']);
            $data['description'] = re_substr($description, 0, 100, true);
        }
        //>给文章插图添加水印
        $firstImage = $this->getCover($data['markdown']);
        //>如果没有上传封面图
        if(!NotEstr($data['cover'])){
            $data['cover'] = $firstImage;
        }
        //>把markdown转html
        $data['html'] = markdown_to_html($data['markdown']);
        //>标签id赋值
        $tag_ids = isset($data['tag_ids']) ? $data['tag_ids'] : [];
        //>删除标签
        if(isset($data['tag_ids']))
            unset($data['tag_ids']);
        //>添加数据
        $result = parent::storeData($data);
        if($result){
            //>给文章添加标签
            $articleTag = new ArticleTag();
            $articleTag->addTagIds($result,$tag_ids);
        }
    }

    /**
     * 给文章的插图添加水印;并取第一张图片作为封面图
     *
     * @param $content        markdown格式的文章内容
     * @param array $except   忽略加水印的图片
     * @return string
     */
    public function getCover($content,$except = array()){
        //>匹配出文章的全部图片
        preg_match_all('/!\[.*?\]\((\S*).*\)/i',$content,$images);
        //>取第一张图片作为封面图
        if (empty($images[1])) {
            $cover = 'uploads/article/default.png';
        } else {
            //>循环给图片添加水印
            foreach ($images[1] as $k=>$v){
                $image = explode(' ', $v);
                $file = public_path().$image[0];
                if (file_exists($file) && !in_array($v, $except)) {
                    Add_text_water($file, cache('config')->get('TEXT_WATER_WORD'));
                }
                // 取第一张图片作为封面图
                if ($k == 0) {
                    $cover = $image[0];
                }
            }
        }
        return $cover;
    }
}
