<?php
/**
 * This is the ajax function to handle the article page
 * The JS sends the input (filter item / action)
 * The filter makes an array of results
 * The results are passed to the lister
 * The lister formats the response using the array
 * The lister passes the response to the JS
 * The JS renders the response
 * 
 * TODO: 
 * Refactor this whole script with twig/timber but not looping every post to collect their tags, use Advanced custom fields. This can be done after the service refactor
 **/

// Is the AJAX controller, takes AJAX data snd returns it
function start_ajax()
{

    // Gets and cleans data
    $instr = isset($_POST['instr']) ? $_POST['instr'] : null; // This is the instruction given, e.g init
    $data = isset($_POST['data']) ? $_POST['data'] : null; // This is the data
    $instr = filter_var($instr, FILTER_SANITIZE_STRING);
    $data = filter_var($data, FILTER_SANITIZE_STRING);

    // Gets the instruction and runs the function it corresponds to
    switch ($instr) {
        case "init":
            echo initFilterArticle();
            break;
        case "selc":
            echo select($data);
            break;
    }

    // Resets AJAX gets ready for next clean request
    die();
}
add_action('wp_ajax_nopriv_start_ajax', 'start_ajax');
add_action('wp_ajax_start_ajax', 'start_ajax');

// Calls the filter on page load to render with correct data
function initFilterArticle()
{

    // Builds objects - this is snowflake code, should be refactored at some point
    $product_obj = new articleFilter("product", "Product");
    $service_obj = new articleFilter("service", "Service");
    $support_obj = new articleFilter("support-type", "Support type");

    // Holds array of article objects
    $subcat_array = array();
    $subcat_array[] = $product_obj;
    $subcat_array[] = $service_obj;
    $subcat_array[] = $support_obj;

    foreach ($subcat_array as $value_obj) { // Fills objects tags array
        $get_my_tags = get_my_tags_article($value_obj); //  Should append subcats to array in 2d associatave array
    }
    return json_encode($subcat_array);
}

// Gets objects cat and fulls its tags array
function get_my_tags_article($obj)
{
    // Makes a query based on category, gets the tags from the posts in the category and outputs them into the dropdown filter
    $args = array( // The args
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'category_name' => 'Helpful articles', // Filters to only articles of this category
        'tag' => $obj->get_cat(), // Post must include the cat
    );
    $custom_query = new WP_query($args); // Custom query
    if ($custom_query->have_posts()):
        while ($custom_query->have_posts()): $custom_query->the_post();
            $id = get_the_ID(); // Gets post ID
            $tags_array = wp_get_post_tags($id); // Gets the tags from the ID
            foreach ($tags_array as $value) { // For each tag in the array
                if ($value->slug != $obj->get_cat()) {
                    if (!in_array($value, $obj->get_tags())) { // If the tag is not already in categories array
                        $obj->push_tags($value); // Pushes tag to category
                    }
                }
            }
        endwhile;
    endif;
    wp_reset_postdata();
}

// runs listArticles, sends it correct data
function select($data)
{
    echo listArticles($data);
}

// Takes the post array, formats and renders it, returns it to the JS
function listArticles($myTag)
{
    $categories = array('Helpful articles');

    if (isset($myTag)) {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'category_name' => $categories[0],
            'tag_id' => $myTag,
        );
    } else {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'category_name' => $categories[0],
        );
    }
    $custom_query = new WP_query($args);

    ?>

    <?php if ($custom_query->have_posts()): ?>
        <?php $counter = 1;?>
        <div class='articles_container small-12 medium-9 cell article_container'>
            <ul>
                <!-- the loop -->
                <?php while ($custom_query->have_posts()): $custom_query->the_post();?>
	                    <?php if ($counter == 1 && has_post_thumbnail()) {?>
	                        <li class='article_self'>
	                            <div class='article_content'>
	                                <div class='article_text'>
	                                    <a href="<?php the_permalink();?>"><h3 class='article_head'><?php the_title();?></h3></a>
	                                    <div class='date'><?php echo get_the_date("d F Y"); ?></div>
	                                    <span class='article_exerpt'><?php echo excerpt(55); ?></span>
	                                    <a class='hollow button excerpt-read-more' href="<?php the_permalink();?>">Read more</a>
	                                </div>
	                                <div class='article_thumbnail'>
	                                    <?php the_post_thumbnail('medium')?>
	                                </div>
	                            </div>
	                        </li>
	                        <hr />
	                        <?php $counter++;?>
	                    <?php } elseif (has_post_thumbnail()){?>
	                        <li class='article_self'>
	                            <div class='article_content'>
	                                <div class='article_text'>
	                                    <a href="<?php the_permalink();?>"><h3 class='article_head'><?php the_title();?></h3></a>
	                                    <div class='date'><?php echo get_the_date("d F Y"); ?></div>
	                                    <span class='article_exerpt'><?php echo excerpt(20); ?></span>
	                                    <a class='hollow button excerpt-read-more' href="<?php the_permalink();?>">Read more</a>
	                                </div>
	                                <div class='article_thumbnail'>
	                                    <?php the_post_thumbnail('medium')?>
	                                </div>
	                            </div>
	                        </li>
	                        <hr />
	                        <?php $counter++;
                        }
                        else {?>
                            <li class='article_self article_self_nothumb'>
	                            <div class='article_content'>
	                                <div class='article_text'>
	                                    <a href="<?php the_permalink();?>"><h3 class='article_head'><?php the_title();?></h3></a>
	                                    <div class='date'><?php echo get_the_date("d F Y"); ?></div>
	                                    <span class='article_exerpt'><?php echo excerpt(20); ?></span>
	                                    <a class='hollow button excerpt-read-more' href="<?php the_permalink();?>">Read more</a>
	                                </div>
	                            </div>
	                        </li>
	                        <hr />
                        <?php $counter++;
                    };?>
	                <?php endwhile;?>
                <!-- end of the loop -->
            </ul>
        </div>

        <?php wp_reset_postdata();?>

    <?php else: ?>
        <p><?php _e('It seems we have no articles for you to see! Please check back later.');?></p>
    <?php endif;?><?php
}

class articleFilter
{

    // Vars
    public $cat;
    public $tags;
    public $status;
    public $title;

    // Get Set Construct
    public function get_cat()
    {
        return $this->cat;
    }
    public function set_cat($new_cat)
    {
        $this->cat = $new_cat;
    }
    public function get_tags()
    {
        return $this->tags;
    }
    public function set_tags($new_tags)
    {
        $this->tags = $new_tags;
    }
    public function get_status()
    {
        return $this->status;
    }
    public function set_status($new_status)
    {
        $this->status = $new_status;
    }
    public function get_title()
    {
        return $this->title;
    }
    public function set_title($new_title)
    {
        $this->title = $new_title;
    }
    public function __construct($cat, $title)
    {
        $this->cat = $cat;
        $this->title = $title;
    }

    // Methods
    public function push_tags($new_tag)
    {
        $this->tags[] = $new_tag;
    }

}

?>