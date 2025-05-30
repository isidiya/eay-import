<?php

namespace Themes\bayan\controllers;

use App\Http\Controllers\Controller;
use App\Models\article;
use App\Models\article_archive;
use App\Models\article_multi_section;
use App\Models\article_multi_section_archive;
use App\Models\image_archive;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BayanController extends Controller {

    public static function insertArticle($article_path=''){



        $article_json = file_get_contents($article_path);
        $article_archive = json_decode($article_json,true);

        echo $article_archive['_id'] . PHP_EOL;
        echo $article_archive['published'] . PHP_EOL;


	$permalink = str_replace('https://www.emaratalyoum.com/','',$article_archive['link']);
        $is_found_article = article_archive::where('permalink',$permalink)->first();

        if($is_found_article){
            echo $article_archive['_id'] . PHP_EOL;
            return true;
        }

//        $article_archive['body'] = '';
//        $article_archive['images'] = '';
        $article = [];
        $article['old_article_id'] = $article_archive['_id'];


//        if($article['old_article_id'] != '1.4740001' ){
//            return true;
//        }

        $article['np_article_id'] = 0;
        $article['article_title'] = $article_archive['title'];
        $article['article_subtitle'] = isset($article_archive['subTitle']) ? $article_archive['subTitle'] : '';
        $article['seo_meta_description'] = isset($article_archive['lead']) ? $article_archive['lead'] : '';
        $article['article_headline'] = isset($article_archive['shortTitle']) ? $article_archive['shortTitle'] : '';
//        $article['permalink'] = str_replace('https://www.albayan.ae/','',$article_archive['link']);
	$article['permalink'] = $permalink;

        $article['article_byline'] = isset($article_archive['authorName']) ? $article_archive['authorName'] : '';


        if(isset($article_archive['authors'][0]['title'])){
            $article['author_name'] = $article_archive['authors'][0]['title'];
        }elseif(isset($article_archive['authorName'])) {
            $article['author_name'] = $article_archive['authorName'];
        } else{
            $article['author_name'] = '';
        }


        if(isset($article_archive['tags']) && is_array($article_archive['tags'])){
            $article['article_tags'] = isset($article_archive['tags']) ?  implode(',',$article_archive['tags']): '';
        }else{
            $article['article_tags'] = isset($article_archive['tags']) ?   $article_archive['tags'] : '';
        }


        $article['publish_time'] = \Carbon\Carbon::parse($article_archive['published'])->toDateTimeString();
        $article['alt_publish_time'] = \Carbon\Carbon::parse($article_archive['created'])->toDateTimeString();
        $article['last_edited'] = \Carbon\Carbon::parse($article_archive['modified'])->toDateTimeString();




        $article['article_body'] = isset($article_archive['body']) ? $article_archive['body'] : '';

        $article['section_name'] = '';
        $article['sub_section_name'] = '';
        $article['image_path'] = '';



        $article_custom_field = $article_archive;
        $unset_array = array('title','subTitle','body','link','authorName','published','created','modified','images','tags','shortTitle','lead','authors');
        foreach($unset_array as $unset){
            if(isset($article_custom_field[$unset])){
                unset($article_custom_field[$unset]);
            }
        }
        $article['article_custom_fields'] = json_encode($article_custom_field);

        $article_multi_sections= [];

        $permalink_array = explode('/',$article['permalink']);


        if(isset($permalink_array[3])){
            $section = \DB::table('section_mapping')->where('section',$permalink_array[3])->first();
            if($section){
                $article['section_name'] = $section->arabic_name;
                $article_multi_sections[] = $section->arabic_name;

            }
        }

        if(isset($permalink_array[4])){
            $sub_section = \DB::table('section_mapping')->where('sub_section',$permalink_array[4])->first();
            if($sub_section){
                $article['sub_section_name'] = $sub_section->arabic_name;
                $article_multi_sections[] = $sub_section->arabic_name;
            }
        }

        if(isset($permalink_array[5])){
            $sub_section_2 = \DB::table('section_mapping')->where('sub_sub_section',$permalink_array[5])->first();
            if($sub_section_2){
                $article['sub_section_name'] = $sub_section_2->arabic_name;
                $article_multi_sections[] = $sub_section_2->arabic_name;
            }
        }


        if(isset($permalink_array[6])){
            $sub_section_3 = \DB::table('section_mapping')->where('sub_sub_sub_section',$permalink_array[6])->first();
            if($sub_section_3){
                $article['sub_section_name'] = $sub_section_3->arabic_name;
                $article_multi_sections[] = $sub_section_3->arabic_name;
            }
        }

//        dd($article['section_name'],$article['sub_section_name'],$permalink_array);
$article['permalink'] = str_replace('https://www.albayan.ae/','',$article_archive['link']);


        if(!empty($article_archive['topImages']) && !empty($article_archive['topImages'][0])){
            $image_path = [];

            if($article_archive['topImages'][0]['imageType'] == 'image'){
                $image_path['media_type'] = 0;
            }

            if(isset($article_archive['topImages'][0]['link'])){
                $image_path['title'] = $article_archive['topImages'][0]['title'];
            }

            if(isset($article_archive['topImages'][0]['link'])){
                $image_path['desc'] = $article_archive['topImages'][0]['desc'];
            }

            if(isset($article_archive['topImages'][0]['link'])){
                $image_path['desc'] = $article_archive['topImages'][0]['desc'];
            }

            $image_date = $article_archive['published'];
            $image_date = Carbon::parse($image_date)->addHours(4);;
            $image_extension  = pathinfo($article_archive['topImages'][0]['link'], PATHINFO_EXTENSION);
            $article_image_path = 'albayan/uploads/archives/images/' . $image_date->format('Y') . '/' . $image_date->format('m') . '/' .  $image_date->format('d') .'/' . str_replace('1.','',$article_archive['topImages'][0]['id']). '.'.$image_extension ;
            $image_path['image_path'] = $article_image_path;
            $article['image_path'] = json_encode($image_path);
        }elseif(!empty($article_archive['images']) && !empty($article_archive['images'][0])){
            $image_path = [];


            $image_path['image_path'] = $article_archive['images'][0]['link'];
            if($article_archive['images'][0]['imageType'] == 'image'){
                $image_path['media_type'] = 0;
            }

            if(isset($article_archive['images'][0]['link'])){
                $image_path['title'] = $article_archive['images'][0]['title'];
            }

            if(isset($article_archive['images'][0]['link'])){
                $image_path['desc'] = $article_archive['images'][0]['desc'];
            }

            if(isset($article_archive['images'][0]['link'])){
                $image_path['desc'] = $article_archive['images'][0]['desc'];
            }

            $image_date = $article_archive['published'];
            $image_date = Carbon::parse($image_date)->addHours(4);;
            $image_extension  = pathinfo($article_archive['images'][0]['link'], PATHINFO_EXTENSION);
            $article_image_path = 'albayan/uploads/archives/images/' . $image_date->format('Y') . '/' . $image_date->format('m') . '/' .  $image_date->format('d') .'/' . str_replace('1.','',$article_archive['images'][0]['id']). '.'.$image_extension ;
            $image_path['image_path'] = $article_image_path;


            $article['image_path'] = json_encode($image_path);
        }



        $cms_article_id = article_archive::insertGetId([
            'np_article_id' => $article['np_article_id'],
            'old_article_id' => $article['old_article_id'],
            'article_title' => $article['article_title'],
            'article_subtitle' => $article['article_subtitle'],
            'seo_meta_description' => $article['seo_meta_description'],
            'article_headline' => $article['article_headline'],
            'permalink' => $article['permalink'],
            'author_name' => $article['author_name'],
            'article_byline' => $article['article_byline'],
            'publish_time' => $article['publish_time'],
            'alt_publish_time' => $article['alt_publish_time'],
            'last_edited' => $article['last_edited'],
            'article_body' => $article['article_body'],
            'section_name' => $article['section_name'],
            'sub_section_name' => $article['sub_section_name'],
            'article_tags' => $article['article_tags'],
//            'sub_section_name_2' => $article['sub_section_name_2'],
//            'sub_section_name_3' => $article['sub_section_name_3'],
            'article_custom_fields' => $article['article_custom_fields'],
            'image_path' => $article['image_path'],
        ]);


        foreach($article_multi_sections as $article_multi_section_key => $article_multi_section){
            if($article_multi_section_key==0 || isset($article_multi_sections[$article_multi_section_key + 1])){
                article_multi_section_archive::insert([
                    'ams_article_id'=>$cms_article_id,
                    'section_name'=>$article_multi_section,
                    'sub_section_name'=>isset($article_multi_sections[$article_multi_section_key+1]) ? $article_multi_sections[$article_multi_section_key+1] : '',
                    'ams_order'=>$article_multi_section_key + 1,
                ]);
            }

        }




        if(!empty($article_archive['images'])){
            foreach($article_archive['images'] as $image_key => $image){
                $article['images'][$image_key]['np_image_id']               = 0;
                $article['images'][$image_key]['np_related_article_id']     = $cms_article_id;
                $article['images'][$image_key]['image_caption']             = isset($image['byline']) ? $image['byline'] : '';
                $article['images'][$image_key]['image_description']         = isset($image['desc']) ? $image['desc'] : '';
                $article['images'][$image_key]['image_path']                = isset($image['link']) ? $image['link'] : '';

                $image_custom_field = $image;
                $image_unset_array = array('title','desc','link');
                foreach($image_unset_array as $image_unset){
                    if(isset($image_custom_field[$image_unset])){
                        unset($image_custom_field[$image_unset]);
                    }
                }

                if($image['imageType'] == 'image'){
                    $media_type = 0;
                }else{
                    $media_type = 1;
                }

                $article['images'][$image_key]['image_custom_field']         = json_encode($image_custom_field);
                $article['images'][$image_key]['media_type']                = $media_type;

            }

            foreach($article['images'] as $image_archive){
                image_archive::insert([
                    'np_image_id'           =>$image_archive['np_image_id'],
                    'np_related_article_id' =>$image_archive['np_related_article_id'],
                    'image_caption'         =>$image_archive['image_caption'],
                    'image_description'     =>$image_archive['image_description'],
                    'image_path'            =>$image_archive['image_path'],
                    'media_type'            =>$image_archive['media_type'],
                    'image_custom_fields'   =>$image_archive['image_custom_field'],
                ]);
            }
        }

        if(isset($article_archive['tags'])){
            foreach($article_archive['tags'] as $tag){
                \DB::table('article_archive_tags')->insert([
                    'cms_article_id' =>$cms_article_id,
                    'tag'            =>trim($tag),
                ]);
            }
        }



    }

    public static function updateArticle($article_path=''){



        $article_json = file_get_contents($article_path);
        $article_archive = json_decode($article_json,true);

        echo $article_archive['_id'] . PHP_EOL;
        echo $article_archive['published'] . PHP_EOL;


        $article = [];
        $article['old_article_id'] = $article_archive['_id'];


        $article_archive_db = article_archive::where('old_article_id',$article['old_article_id'])->first();

        if(empty($article_archive_db)){
            return true;
        }

//        if($article['old_article_id'] != '1.4740001' ){
//            return true;
//        }





        $article['image_path'] = '';


        $article_custom_field = $article_archive;
        $unset_array = array('title','subTitle','body','link','authorName','published','created','modified','images','tags','shortTitle','lead','authors');
        foreach($unset_array as $unset){
            if(isset($article_custom_field[$unset])){
                unset($article_custom_field[$unset]);
            }
        }
        $article['article_custom_fields'] = json_encode($article_custom_field);



        if(!empty($article_archive['topImages']) && !empty($article_archive['topImages'][0])){
            $image_path = [];

            if($article_archive['topImages'][0]['imageType'] == 'image'){
                $image_path['media_type'] = 0;
            }

            if(isset($article_archive['topImages'][0]['link'])){
                $image_path['title'] = $article_archive['topImages'][0]['title'];
            }

            if(isset($article_archive['topImages'][0]['link'])){
                $image_path['desc'] = $article_archive['topImages'][0]['desc'];
            }

            if(isset($article_archive['topImages'][0]['link'])){
                $image_path['desc'] = $article_archive['topImages'][0]['desc'];
            }

            $image_date = $article_archive['published'];
            $image_date = Carbon::parse($image_date)->addHours(4);;
            $image_extension  = pathinfo($article_archive['topImages'][0]['link'], PATHINFO_EXTENSION);
            $article_image_path = 'albayan/uploads/archives/images/' . $image_date->format('Y') . '/' . $image_date->format('m') . '/' .  $image_date->format('d') .'/' . str_replace('1.','',$article_archive['topImages'][0]['id']). '.'.$image_extension ;
            $image_path['image_path'] = $article_image_path;
            $article['image_path'] = json_encode($image_path);
        }elseif(!empty($article_archive['images']) && !empty($article_archive['images'][0])){
            $image_path = [];


            $image_path['image_path'] = $article_archive['images'][0]['link'];
            if($article_archive['images'][0]['imageType'] == 'image'){
                $image_path['media_type'] = 0;
            }

            if(isset($article_archive['images'][0]['link'])){
                $image_path['title'] = $article_archive['images'][0]['title'];
            }

            if(isset($article_archive['images'][0]['link'])){
                $image_path['desc'] = $article_archive['images'][0]['desc'];
            }

            if(isset($article_archive['images'][0]['link'])){
                $image_path['desc'] = $article_archive['images'][0]['desc'];
            }

            $image_date = $article_archive['published'];
            $image_date = Carbon::parse($image_date)->addHours(4);;
            $image_extension  = pathinfo($article_archive['images'][0]['link'], PATHINFO_EXTENSION);
            $article_image_path = 'albayan/uploads/archives/images/' . $image_date->format('Y') . '/' . $image_date->format('m') . '/' .  $image_date->format('d') .'/' . str_replace('1.','',$article_archive['images'][0]['id']). '.'.$image_extension ;
            $image_path['image_path'] = $article_image_path;


            $article['image_path'] = json_encode($image_path);
        }else{
            return true;
        }





        article_archive::where('cms_article_id',$article_archive_db->cms_article_id)->update([
            'image_path' => $article['image_path'],
            'is_updated' => 1,
        ]);

    }


    public static function updateArticle_2($article_path=''){



        $article_json = file_get_contents($article_path);
        $article_archive = json_decode($article_json,true);

        echo $article_archive['_id'] . PHP_EOL;
        echo $article_archive['published'] . PHP_EOL;


        $article = [];
        $article['old_article_id'] = $article_archive['_id'];


        $article_archive_db = article_archive::where('old_article_id',$article['old_article_id'])->first();

        if(empty($article_archive_db)){
            return true;
        }

//        if($article['old_article_id'] != '1.4740001' ){
//            return true;
//        }




        $article['publish_time'] = \Carbon\Carbon::parse($article_archive['published'])->toDateTimeString();
        $article['alt_publish_time'] = \Carbon\Carbon::parse($article_archive['created'])->toDateTimeString();
        $article['last_edited'] = \Carbon\Carbon::parse($article_archive['modified'])->toDateTimeString();


        article_archive::where('cms_article_id',$article_archive_db->cms_article_id)->update([
            'publish_time' => $article['publish_time'],
            'alt_publish_time' => $article['alt_publish_time'],
            'last_edited' => $article['last_edited'],
            'is_updated' => 1,
        ]);


        dd($article_archive_db->cms_article_id,$article['publish_time']);


        if(!empty($article_archive['images'])){
            foreach($article_archive['images'] as $image_key => $image){
                $article['images'][$image_key]['np_image_id']               = 0;
                $article['images'][$image_key]['np_related_article_id']     = $article_archive_db->cms_article_id;
                $article['images'][$image_key]['image_caption']             = isset($image['byline']) ? $image['byline'] : '';
                $article['images'][$image_key]['image_description']         = isset($image['desc']) ? $image['desc'] : '';
                $article['images'][$image_key]['image_path']                = isset($image['link']) ? $image['link'] : '';

                $image_custom_field = $image;
                $image_unset_array = array('title','desc','link');
                foreach($image_unset_array as $image_unset){
                    if(isset($image_custom_field[$image_unset])){
                        unset($image_custom_field[$image_unset]);
                    }
                }

                if($image['imageType'] == 'image'){
                    $media_type = 0;
                }else{
                    $media_type = 1;
                }

                $article['images'][$image_key]['image_custom_field']         = json_encode($image_custom_field);
                $article['images'][$image_key]['media_type']                = $media_type;

            }

            foreach($article['images'] as $image_archive){
                image_archive::insert([
                    'np_image_id'           =>$image_archive['np_image_id'],
                    'np_related_article_id' =>$image_archive['np_related_article_id'],
                    'image_caption'         =>$image_archive['image_caption'],
                    'image_description'     =>$image_archive['image_description'],
                    'image_path'            =>$image_archive['image_path'],
                    'media_type'            =>$image_archive['media_type'],
                    'image_custom_fields'   =>$image_archive['image_custom_field'],
                ]);
            }
        }





    }

    public static function updateArticle_backup($article_path=''){



        $article_json = file_get_contents($article_path);
        $article_archive = json_decode($article_json,true);

        echo $article_archive['_id'] . PHP_EOL;
        echo $article_archive['published'] . PHP_EOL;


        $article = [];
        $article['old_article_id'] = $article_archive['_id'];


        $article_archive_db = article_archive::where('old_article_id',$article['old_article_id'])->first();

        if(empty($article_archive_db)){
            return true;
        }

//        if($article['old_article_id'] != '1.4740001' ){
//            return true;
//        }



        if(isset($article_archive['authors'][0]['title'])){
            $article['author_name'] = $article_archive['authors'][0]['title'];
        }elseif(isset($article_archive['authorName'])) {
            $article['author_name'] = $article_archive['authorName'];
        } else{
            $article['author_name'] = '';
        }

        $article['publish_time'] = $article_archive['published'];
        $article['alt_publish_time'] = $article_archive['created'];
        $article['last_edited'] = $article_archive['modified'];


        $article['image_path'] = '';


        $article_custom_field = $article_archive;
        $unset_array = array('title','subTitle','body','link','authorName','published','created','modified','images','tags','shortTitle','lead','authors');
        foreach($unset_array as $unset){
            if(isset($article_custom_field[$unset])){
                unset($article_custom_field[$unset]);
            }
        }
        $article['article_custom_fields'] = json_encode($article_custom_field);



        if(!empty($article_archive['topImages']) && !empty($article_archive['topImages'][0])){
            $image_path = [];
            $image_path['image_path'] = $article_archive['topImages'][0]['link'];
            if($article_archive['topImages'][0]['imageType'] == 'image'){
                $image_path['media_type'] = 0;
            }

            if(isset($article_archive['topImages'][0]['link'])){
                $image_path['title'] = $article_archive['topImages'][0]['title'];
            }

            if(isset($article_archive['topImages'][0]['link'])){
                $image_path['desc'] = $article_archive['topImages'][0]['desc'];
            }

            if(isset($article_archive['topImages'][0]['link'])){
                $image_path['desc'] = $article_archive['topImages'][0]['desc'];
            }


            $article['image_path'] = json_encode($image_path);
        }elseif(!empty($article_archive['images']) && !empty($article_archive['images'][0])){
            $image_path = [];
            $image_path['image_path'] = $article_archive['images'][0]['link'];
            if($article_archive['images'][0]['imageType'] == 'image'){
                $image_path['media_type'] = 0;
            }

            if(isset($article_archive['images'][0]['link'])){
                $image_path['title'] = $article_archive['images'][0]['title'];
            }

            if(isset($article_archive['images'][0]['link'])){
                $image_path['desc'] = $article_archive['images'][0]['desc'];
            }

            if(isset($article_archive['images'][0]['link'])){
                $image_path['desc'] = $article_archive['images'][0]['desc'];
            }


            $article['image_path'] = json_encode($image_path);
        }





        article_archive::where('cms_article_id',$article_archive_db->cms_article_id)->update([
            'author_name' => $article['author_name'],
            'publish_time' => $article['publish_time'],
            'alt_publish_time' => $article['alt_publish_time'],
            'last_edited' => $article['last_edited'],
            'image_path' => $article['image_path'],
            'is_updated' => 1,
            'article_custom_fields' => $article['article_custom_fields'],
        ]);




        if(!empty($article_archive['images'])){
            foreach($article_archive['images'] as $image_key => $image){
                $article['images'][$image_key]['np_image_id']               = 0;
                $article['images'][$image_key]['np_related_article_id']     = $article_archive_db->cms_article_id;
                $article['images'][$image_key]['image_caption']             = isset($image['byline']) ? $image['byline'] : '';
                $article['images'][$image_key]['image_description']         = isset($image['desc']) ? $image['desc'] : '';
                $article['images'][$image_key]['image_path']                = isset($image['link']) ? $image['link'] : '';

                $image_custom_field = $image;
                $image_unset_array = array('title','desc','link');
                foreach($image_unset_array as $image_unset){
                    if(isset($image_custom_field[$image_unset])){
                        unset($image_custom_field[$image_unset]);
                    }
                }

                if($image['imageType'] == 'image'){
                    $media_type = 0;
                }else{
                    $media_type = 1;
                }

                $article['images'][$image_key]['image_custom_field']         = json_encode($image_custom_field);
                $article['images'][$image_key]['media_type']                = $media_type;

            }

            foreach($article['images'] as $image_archive){
                image_archive::insert([
                    'np_image_id'           =>$image_archive['np_image_id'],
                    'np_related_article_id' =>$image_archive['np_related_article_id'],
                    'image_caption'         =>$image_archive['image_caption'],
                    'image_description'     =>$image_archive['image_description'],
                    'image_path'            =>$image_archive['image_path'],
                    'media_type'            =>$image_archive['media_type'],
                    'image_custom_fields'   =>$image_archive['image_custom_field'],
                    'image_custom_fields'   =>$image_archive['image_custom_field'],
                ]);
            }
        }





    }
	


	public static function updateArticleSections($article_archive){


//dd($article_archive);
        if(empty($article_archive) || $article_archive->section_id != 0){
            echo 'exit: ' . $article_archive->cms_article_id . PHP_EOL;
            return true;
        }

echo 'in: ' . $article_archive->cms_article_id . PHP_EOL;


//        if($article['old_article_id'] != '1.4740001' ){
//            return true;
//        }








        $article['section_name'] = '';
        $article['sub_section_name'] = '';




        $article_multi_sections= [];

        $permalink_array = explode('/',$article_archive->permalink);


        if(isset($permalink_array[0])){
            $section = \DB::table('section_mapping')->where('section',$permalink_array[0])->first();
            if($section){
                $article['section_name'] = $section->arabic_name;
                $article_multi_sections[] = $section->arabic_name;

            }
        }

        if(isset($permalink_array[1])){
            $sub_section = \DB::table('section_mapping')->where('sub_section',$permalink_array[1])->first();
            if($sub_section){
                $article['sub_section_name'] = $sub_section->arabic_name;
                $article_multi_sections[] = $sub_section->arabic_name;
            }
        }

        if(isset($permalink_array[2])){
            $sub_section_2 = \DB::table('section_mapping')->where('sub_sub_section',$permalink_array[2])->first();
            if($sub_section_2){
                $article['sub_section_name'] = $sub_section_2->arabic_name;
                $article_multi_sections[] = $sub_section_2->arabic_name;
            }
        }


        if(isset($permalink_array[3])){
            $sub_section_3 = \DB::table('section_mapping')->where('sub_sub_sub_section',$permalink_array[3])->first();
            if($sub_section_3){
                $article['sub_section_name'] = $sub_section_3->arabic_name;
                $article_multi_sections[] = $sub_section_3->arabic_name;
            }
        }


        $cms_article_id = article_archive::where('cms_article_id',$article_archive->cms_article_id)->update([
            'section_name' => $article['section_name'],
            'sub_section_name' => $article['sub_section_name'],
        ]);


        foreach($article_multi_sections as $article_multi_section_key => $article_multi_section){
            if($article_multi_section_key==0 || isset($article_multi_sections[$article_multi_section_key + 1])){
                article_multi_section_archive::insert([
                    'ams_article_id'=>$article_archive->cms_article_id,
                    'section_name'=>$article_multi_section,
                    'sub_section_name'=>isset($article_multi_sections[$article_multi_section_key+1]) ? $article_multi_sections[$article_multi_section_key+1] : '',
                    'ams_order'=>$article_multi_section_key + 1,
                ]);
            }

        }

    }


}
