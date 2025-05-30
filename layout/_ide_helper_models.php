<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\article
 *
 * @property int $cms_article_id cms_article_id : ID of article in CMS, 0 if it is a new article, >0 in case update or delete
 * @property int|null $np_article_id np_article_id : ID of article in Newspress
 * @property string|null $article_name article_name : Article Name
 * @property string $article_title article_title : Article Title
 * @property string|null $article_headline article_headline : Article Headline
 * @property string|null $article_subtitle article_subtitle : Article Subtitle
 * @property string|null $article_body article_body : Article Body
 * @property string|null $article_custom_fields article_custom_fields : Custom data for the article. JSON array used  for some custom fields to be added
 * @property string|null $cms_type cms_type : CMS Type ( live or staging )
 * @property int $author_id
 * @property int|null $section_id
 * @property string|null $seo_meta_keywords
 * @property string|null $seo_meta_description
 * @property string|null $seo_meta_title
 * @property string|null $publish_time
 * @property string|null $related_articles_ids (JSON Array contains CMS Ids for the related articles)
 * @property string|null $article_tags
 * @property int|null $sub_section_id
 * @property int $visit_count
 * @property int $sponsored_flag
 * @property int|null $offer_flag
 * @property int|null $featured_article_flag
 * @property int|null $media_gallery_flag
 * @property int $video_gallery_flag
 * @property int|null $highlight_flag
 * @property int|null $top_story_flag
 * @property int $is_updated
 * @property int $is_old_article
 * @property int $old_article_id
 * @property string $article_byline
 * @property string|null $ts
 * @property string $last_edited
 * @property string $alt_publish_time
 * @property string|null $image_path
 * @property string $author_name
 * @property string $section_name
 * @property string $sub_section_name
 * @property int $slide_show
 * @property int|null $breaking_news
 * @property string|null $visit_count_update_date
 * @property int|null $old_cms_article_id
 * @property string|null $permalink
 * @property-read mixed $author_image_src
 * @property-read mixed $author_url
 * @property-read mixed $custom_fields
 * @property-read mixed $image_alt_text
 * @property-read mixed $image_src
 * @property-read mixed $is_author_section
 * @property-read mixed $related_images
 * @property-read mixed $section_url
 * @property-read mixed $seo_title
 * @property-read mixed $seo_url
 * @property-read mixed $short_title
 * @property-read mixed $simple_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\image[] $image
 * @property-read \App\Models\section $section
 * @property-read \App\Models\sub_section $sub_section
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\article_tags[] $tag
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereAltPublishTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereArticleBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereArticleByline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereArticleCustomFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereArticleHeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereArticleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereArticleSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereArticleTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereArticleTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereAuthorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereBreakingNews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereCmsArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereCmsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereFeaturedArticleFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereHighlightFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereIsOldArticle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereIsUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereLastEdited($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereMediaGalleryFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereNpArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereOfferFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereOldArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereOldCmsArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article wherePermalink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article wherePublishTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereRelatedArticlesIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereSectionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereSeoMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereSeoMetaKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereSeoMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereSlideShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereSponsoredFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereSubSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereSubSectionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereTopStoryFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereTs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereVideoGalleryFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereVisitCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article whereVisitCountUpdateDate($value)
 */
	class article extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\article_author
 *
 * @property int $article_author_id
 * @property int $cms_article_id
 * @property int $np_article_id
 * @property int $np_author_id
 * @property string|null $author_name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_author newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_author newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_author query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_author whereArticleAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_author whereAuthorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_author whereCmsArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_author whereNpArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_author whereNpAuthorId($value)
 */
	class article_author extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\article_multi_section
 *
 * @property int $ams_id
 * @property int $ams_article_id
 * @property int $ams_country_id
 * @property int $ams_section_id
 * @property int $ams_subsection_id
 * @property string $ams_article_date
 * @property-read \App\Models\article $article
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_multi_section newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_multi_section newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_multi_section query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_multi_section whereAmsArticleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_multi_section whereAmsArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_multi_section whereAmsCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_multi_section whereAmsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_multi_section whereAmsSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_multi_section whereAmsSubsectionId($value)
 */
	class article_multi_section extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\article_tags
 *
 * @property int $tag
 * @property int $np_article_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_tags newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_tags newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_tags query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_tags whereNpArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_tags whereTag($value)
 */
	class article_tags extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\article_visit_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_visit_count newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_visit_count newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_visit_count query()
 */
	class article_visit_count extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\article_visit_count_last_update
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_visit_count_last_update newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_visit_count_last_update newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_visit_count_last_update query()
 */
	class article_visit_count_last_update extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\author
 *
 * @property-read mixed $image
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\author newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\author newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\author query()
 */
	class author extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\bootstrap_rows
 *
 * @property int $id
 * @property int|null $page_id
 * @property string|null $bootstrap_tags
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\bootstrap_rows newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\bootstrap_rows newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\bootstrap_rows query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\bootstrap_rows whereBootstrapTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\bootstrap_rows whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\bootstrap_rows wherePageId($value)
 */
	class bootstrap_rows extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\cms_admin
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_admin query()
 */
	class cms_admin extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\cms_general
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_general newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_general newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_general query()
 */
	class cms_general extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\cms_msg
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_msg newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_msg newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_msg query()
 */
	class cms_msg extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\cms_urls
 *
 * @property int $cms_urls_id
 * @property string $cms_urls_tbl
 * @property string $cms_urls_dispatch
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_urls newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_urls newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_urls query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_urls whereCmsUrlsDispatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_urls whereCmsUrlsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\cms_urls whereCmsUrlsTbl($value)
 */
	class cms_urls extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\country
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\country query()
 */
	class country extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\image
 *
 * @property int $cms_image_id cms_image_id : ID of image in CMS, 0 if it is a new image, >0 in case unpublish
 * @property int $np_image_id np_image_id : ID of image in Newspress
 * @property string|null $image_caption image_caption : Image Caption
 * @property int $np_related_article_id np_related_article_id : ID of article that related on this image
 * @property string|null $cms_type cms_type : CMS Type ( live or staging )
 * @property string|null $image_description image_description : Image Description
 * @property string $image_path image_binary : Image binary
 * @property int|null $media_type (1 : image , 2 : youtube links)
 * @property int $is_old_image
 * @property string $small_image
 * @property int $is_updated
 * @property string $image_cropping
 * @property int $media_order
 * @property int $is_copied
 * @property int $image_is_deleted
 * @property string|null $image_alt_text
 * @property-read \App\Models\article $article
 * @property-read mixed $is_video
 * @property-read mixed $src
 * @property-read mixed $video_url
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereCmsImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereCmsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereImageAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereImageCaption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereImageCropping($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereImageDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereImageIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereIsCopied($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereIsOldImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereIsUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereMediaOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereMediaType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereNpImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereNpRelatedArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\image whereSmallImage($value)
 */
	class image extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\menu
 *
 * @property int $cms_menu_id cms_menu_id : ID of menu in CMS
 * @property int $np_menu_id np_menu_id : ID of menu in Newspress
 * @property string|null $cms_type cms_type : CMS Type ( live or staging )
 * @property string $menu_name menu_name : Menu Name
 * @property int|null $publication_id publication_id : publication ID
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\menu_item[] $menu_items
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu whereCmsMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu whereCmsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu whereMenuName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu whereNpMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu wherePublicationId($value)
 */
	class menu extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\menu_item
 *
 * @property int $cms_menu_items_id cms_menu_items_id : ID of menu items in CMS
 * @property int $np_menu_items_id np_menu_items_id : ID of menu items in Newspress
 * @property string|null $cms_type
 * @property string $menu_items_name
 * @property string|null $menu_items_link menu_items_link : Link
 * @property int|null $menu_items_order menu_items_order : Order
 * @property int|null $page_id page_id : Page ID
 * @property int $menu_id menu_id : Menu ID
 * @property int $parent_id
 * @property int $section_id
 * @property int $subsection_id
 * @property string|null $menu_items_media_info
 * @property-read mixed $seo_url
 * @property-read \App\Models\menu $menu
 * @property-read \App\Models\page|null $page
 * @property-read \App\Models\menu_item $parent
 * @property-read \App\Models\section $section
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\menu_item[] $sub_menu_items
 * @property-read \App\Models\sub_section $sub_section
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item whereCmsMenuItemsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item whereCmsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item whereMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item whereMenuItemsLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item whereMenuItemsMediaInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item whereMenuItemsName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item whereMenuItemsOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item whereNpMenuItemsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item whereSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\menu_item whereSubsectionId($value)
 */
	class menu_item extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\modified_aticles
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\modified_aticles newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\modified_aticles newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\modified_aticles query()
 */
	class modified_aticles extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\newsletter
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\newsletter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\newsletter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\newsletter query()
 */
	class newsletter extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\page
 *
 * @property int $cms_page_id
 * @property int $np_page_id np_page_id : ID of page in Newspress
 * @property string|null $cms_type cms_type : CMS Type ( live or staging )
 * @property string|null $page_title ** page_title** : Page Title
 * @property string $page_link page_link : Page Link
 * @property string|null $header_script header_script : Header Script
 * @property string|null $seo_meta_keywords
 * @property string|null $seo_meta_description
 * @property string|null $seo_meta_title
 * @property string $device_type "web", "mobile", or "tablet"
 * @property int|null $is_home_page
 * @property int $is_subscribe_page
 * @property int $send_newsletter
 * @property int|null $page_section_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page whereCmsPageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page whereCmsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page whereHeaderScript($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page whereIsHomePage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page whereIsSubscribePage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page whereNpPageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page wherePageLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page wherePageSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page wherePageTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page whereSendNewsletter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page whereSeoMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page whereSeoMetaKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page whereSeoMetaTitle($value)
 */
	class page extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\page_country
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page_country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page_country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\page_country query()
 */
	class page_country extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\pdf
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\pdf newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\pdf newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\pdf query()
 */
	class pdf extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\poll
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\poll newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\poll newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\poll query()
 */
	class poll extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\related_articles
 *
 * @property int $article_id
 * @property string|null $related_ids
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\related_articles newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\related_articles newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\related_articles query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\related_articles whereArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\related_articles whereRelatedIds($value)
 */
	class related_articles extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\section
 *
 * @property int $cms_section_id
 * @property int|null $np_section_id
 * @property string $section_name
 * @property string|null $cms_type
 * @property string|null $section_info
 * @property int|null $section_order
 * @property string $section_color
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\site_poll_answer[] $sub_section
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\section newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\section newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\section query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\section whereCmsSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\section whereCmsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\section whereNpSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\section whereSectionColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\section whereSectionInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\section whereSectionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\section whereSectionOrder($value)
 */
	class section extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\site_poll
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\site_poll_answer[] $site_poll_answer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\site_poll newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\site_poll newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\site_poll query()
 */
	class site_poll extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\site_poll_answer
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\site_poll_answer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\site_poll_answer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\site_poll_answer query()
 */
	class site_poll_answer extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\site_poll_vote
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\site_poll_vote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\site_poll_vote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\site_poll_vote query()
 */
	class site_poll_vote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\sub_section
 *
 * @property int $cms_sub_section_id
 * @property string $sub_section_name
 * @property string|null $sub_section_info
 * @property int $section_id
 * @property int|null $sub_section_order
 * @property int $np_sub_section_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\sub_section newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\sub_section newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\sub_section query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\sub_section whereCmsSubSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\sub_section whereNpSubSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\sub_section whereSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\sub_section whereSubSectionInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\sub_section whereSubSectionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\sub_section whereSubSectionOrder($value)
 */
	class sub_section extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $user_id
 * @property string $username
 * @property string $password
 * @property string $full_name
 * @property string $phone
 * @property string $mobile
 * @property string $email
 * @property int|null $country_id
 * @property string $birth_date
 * @property string $gender
 * @property string $hobbies
 * @property int $is_business
 * @property int $paid_subscription
 * @property int $paid_subscription_active
 * @property int $is_admin
 * @property int $is_old_user
 * @property string|null $start_date
 * @property string|null $end_date
 * @property string $reset_code
 * @property string $source
 * @property string $category
 * @property float $price
 * @property string|null $misc_notes
 * @property string|null $financial_notes
 * @property int $last_saved_by
 * @property string $last_saved_at
 * @property string $newsletter_sections
 * @property string $fb_id
 * @property string $fb_access_token
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereFbAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereFbId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereFinancialNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereHobbies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereIsBusiness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereIsOldUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastSavedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastSavedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereMiscNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNewsletterSections($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePaidSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePaidSubscriptionActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereResetCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUsername($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\web_data_values
 *
 * @property int $id
 * @property int $cms_article_id
 * @property string|null $data_key
 * @property string|null $data_value
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\web_data_values newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\web_data_values newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\web_data_values query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\web_data_values whereCmsArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\web_data_values whereDataKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\web_data_values whereDataValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\web_data_values whereId($value)
 */
	class web_data_values extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\widget
 *
 * @property int $cms_widget_id cms_widget_id : ID of widget in CMS
 * @property int|null $np_widget_id np_widget_id : ID of widget in Newspress
 * @property int|null $page_id page_id : Page ID
 * @property int|null $widget_col widget_col : widget column
 * @property int|null $widget_row widget_row : widget Row
 * @property string|null $widget_options widget_options : widget options
 * @property string|null $cms_type cms_type : CMS Type ( live or staging )
 * @property string|null $widget_type widget_type : widget type
 * (Facebook, Articles Filter, Static Articles, Twitter, RSS Feed, Menu, Media Gallery, Tabs, WYSIWYG)
 * @property int|null $parent_widget_id
 * @property string|null $widget_style (This is the mapped value for the styles you will define for the widgest)
 * @property string|null $widget_data
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget whereCmsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget whereCmsWidgetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget whereNpWidgetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget whereParentWidgetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget whereWidgetCol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget whereWidgetData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget whereWidgetOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget whereWidgetRow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget whereWidgetStyle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\widget whereWidgetType($value)
 */
	class widget extends \Eloquent {}
}

