<?php
/**
 * This script creates a cron job to run twice daily that will update the jobs list on SWASFT, by generating a new list and removing the old.
 * 
 * Possibly this script can be changed to compare the state of posted jobs vs vacancy array and only update changes.
 */

if (!wp_next_scheduled('update_jobs_hook')) {
    wp_schedule_event(time(), 'twicedaily', 'update_jobs_hook');
}

add_action('update_jobs_hook', 'update_jobs');

/**
 * Updates all the jobs.
 * Will connect via xml, then create an object array of each vacancy with the details added, it will sort the array, delete the old jobs then push each vacancy in the array into posts
 */
function update_jobs()
{
    // Connection URL
    $jobs_URL = "REDACTED";

    // Object array
    $vacancy_array = [];

    $error_message_connect = "An error has been encountered when connecting to REDACTED using simplexml.";
    $xml = simplexml_load_file($jobs_URL) or mail_error($error_message_connect); // Load XML file
    unset($xml->status); // Removes unneeded element

    /**
     * Makes a new object, populates it with data from the XML then appends it to the object array for each vacancy
     */
    foreach ($xml->children() as $vacancy) {
        $error_message = "An error was encountered when creating the my_vacancy object from the vacancy class, this means that the jobs list can't be created.";
        $my_vacancy = new vacancy() or mail_error($error_message); // Makes new vacancy object

        // Setting object attributes with XML data
        $my_vacancy->setJob_title(strval($vacancy->job_title));
        $my_vacancy->setJob_location(strval($vacancy->job_location));
        $my_vacancy->setJob_type(strval($vacancy->job_type));
        $my_vacancy->setJob_salary(strval($vacancy->job_salary));
        $my_vacancy->setJob_closing_date(strval($vacancy->job_closedate));
        $my_vacancy->setJob_reference(strval($vacancy->job_reference));
        $my_vacancy->setJob_url(strval($vacancy->job_url));
        $my_vacancy->setJob_description(strval($vacancy->job_description));
        $my_vacancy->setJob_open_date($vacancy->job_postdate);
        $my_vacancy->setJob_id(strval($vacancy->id));
        $my_vacancy->setJob_staff_group(strval($vacancy->job_staff_group));

        $vacancy_array[] = $my_vacancy; // Appends the object into the object array
    }

    /**
     * Splits vacancy array into 'see advert' and dates in temporary arrays, then sorts the actual date array then merges the arrays together.
     * Lots of methods that should have worked didn't, so this heavy approach was taken.
     */
    $vacancy_nodate = [];
    $vacancy_date = [];

    foreach ($vacancy_array as $key => $value) {
        if ($value->getJob_closing_date() == "See advert") {
            $vacancy_nodate[] = $value;
        } else {
            $vacancy_date[] = $value;
        }
    }

    $vacancy_array = array();

    function date_compare($a, $b)
    {
        $t1 = strtotime($a->getJob_closing_date());
        $t2 = strtotime($b->getJob_closing_date());
        return $t2 - $t1;
    }
    usort($vacancy_date, 'date_compare');

    foreach ($vacancy_nodate as $value) {
        $vacancy_array[] = $value;
    }
    
    foreach ($vacancy_date as $value) {
        // This check is needed due to the first item in the array being a duplicate of the last item and a nodate
        if ( $value->getJob_closing_date() == "See advert" ){ 
            $value->getJob_closing_date() == "See advert";
        }
        else {
            $vacancy_array[] = $value;
        }
        
    }


    /**
     * Removes old posts before new posts are added (allows for validation)
     */
    remove_old_posts();


    /**
     * Posts jobs in array
     */
    foreach ($vacancy_array as $key => $job_post) {
        if (!empty($vacancy_array)) {
            $job_post->post_vacancy();
        } else { // error is vacancy array is empty
            $error_message = "Error encountered when posting jobs to array.<br />
            This error has happened due to there being no jobs in the vacancy object array.<br >
            One of three things is the cause:
            <ul>
                <li>Either the site REDACTED has no jobs</li>
                <li>The site above has changed its structure or element names</li>
                <li>The script has broken at some point</li>
            </ul>";
            mail_error($error_message);
        }
    }
}



/**
 * Mails error message to admin
 *
 * @param string $error_message The error message to display
 */
function mail_error($error_message)
{
    write_log("Error in update_jobs.php: "+$error_message);
    $today = date("Y-m-d H:i:s");
    $to = 'REDACTED';
    $subject = 'SWASFT has encountered an error';
    $body = 'The site <a href="https://swasft.co.uk">https://swasft.co.uk</a> has encountered an error in the refreshing of the job vacancies list. <br />
        The error has provided the following error message: <hr />
        <code>
        ' . $error_message .
        '</code>
        <hr />
        This error was encountered on: ' . $today . "
        <br /> This error comes from REDACTED";
    $headers = array('Content-Type: text/html; charset=UTF-8');
    echo "It seems we have encountered an error, check back later, or contact us."; // User displayed error message

    //wp_mail($to, $subject, $body, $headers);
    wp_die(); // kills the script
}


/**
 * Removes old posts in taxonomy
 */
function remove_old_posts()
{
    $args = array(
        'posts_per_page' => 100,
        'post_status' => 'publish',
        'post_type' => 'job',
    );

    $job_list = get_posts($args);
    foreach ($job_list as $latest_job) {
        $job_id = $latest_job->ID;
        wp_delete_post($job_id);
    }
}


/**
 * Vacancy class
 */
class vacancy
{
    public $job_title = "See advert";
    public $job_location = "See advert";
    public $job_salary = "See advert";
    public $job_type = "See advert";
    public $job_closing_date = "See advert";
    public $job_reference = "See advert";
    public $job_url = "#";
    public $job_description = "See advert";
    public $job_open_date = "See advert";
    public $job_id = "See advert";
    public $job_staff_group = "See advert";

    /**
     * Set the value of job_title
     *
     * @return  self
     */
    public function setJob_title($job_title)
    {
        $this->job_title = $job_title;

        return $this;
    }

    /**
     * Set the value of job_location
     *
     * @return  self
     */
    public function setJob_location($job_location)
    {
        $this->job_location = $job_location;

        return $this;
    }

    /**
     * Set the value of job_salary
     *
     * @return  self
     */
    public function setJob_salary($job_salary)
    {
        $this->job_salary = $job_salary;

        return $this;
    }

    /**
     * Set the value of job_type
     *
     * @return  self
     */
    public function setJob_type($job_type)
    {
        $this->job_type = $job_type;

        return $this;
    }

    /**
     * Set the value of job_closing_date
     *
     * @return  self
     */
    public function setJob_closing_date($job_closing_date)
    {
        if ($job_closing_date != "See advert") {
            $job_closing_date = str_replace('/', '-', $job_closing_date); // Formats date properly
        } else {
            $job_closing_date = "See advert"; // Can be changed before output, used in sort
        }
        $this->job_closing_date = $job_closing_date;

        return $this;
    }

    /**
     * Set the value of job_reference
     *
     * @return  self
     */
    public function setJob_reference($job_reference)
    {
        $this->job_reference = $job_reference;

        return $this;
    }

    /**
     * Set the value of job_url
     *
     * @return  self
     */
    public function setJob_url($job_url)
    {
        $this->job_url = $job_url;

        return $this;
    }

    /**
     * Set the value of job_description
     *
     * @return  self
     */
    public function setJob_description($job_description)
    {
        $this->job_description = $job_description;

        return $this;
    }

    /**
     * Set the value of job_open_date, formats as yyyy-mm-dd
     *
     * @return  self
     */
    public function setJob_open_date($job_open_date)
    {
        $this->job_open_date = $job_open_date;

        return $this;
    }

    /**
     * Set the value of job_id
     *
     * @return  self
     */
    public function setJob_id($job_id)
    {
        $this->job_id = $job_id;

        return $this;
    }

    /**
     * Set the value of job_staff_group
     *
     * @return  self
     */
    public function setJob_staff_group($job_staff_group)
    {
        $this->job_staff_group = $job_staff_group;

        return $this;
    }

    /**
     * Get the value of job_id
     */
    public function getJob_id()
    {
        return $this->job_id;
    }

    /**
     * Get the value of job_closing_date
     */
    public function getJob_closing_date()
    {
        return $this->job_closing_date;
    }

    /**
     * Uploads the vacancy if valid, returns false if not
     *
     */
    public function post_vacancy()
    {
        if (!empty($this->job_title) && !empty($this->job_reference) && !empty($this->job_url) && !empty($this->job_id)) {

            // Create post object
            $my_post = array(
                'post_title' => $this->job_title,
                'post_content' => $this->job_description,
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'job',
            );

            // Insert the post into the database
            $post_id = wp_insert_post($my_post);
            update_field("job_id", $this->job_id, $post_id);
            update_field("job_location", $this->job_location, $post_id);
            update_field("job_salary", $this->job_salary, $post_id);
            update_field("job_type", $this->job_type, $post_id);
            update_field("job_closedate", $this->job_closing_date, $post_id);
            update_field("job_reference", $this->job_reference, $post_id);
            update_field("job_staff_group", $this->job_staff_group, $post_id);
            update_field("job_url", $this->job_url, $post_id);
        } else {
            $error_message = "Data missing in Class: vacancy Method: post_vacancy(). <br />
                Validation error, list of checked variables:<br />
                <ul>
                <li>job_title: " . $this->job_title . "</li>
                <li>job_reference: " . $this->job_reference . "</li>
                <li>job_url: " . $this->job_url . "</li>
                <li>job_id: " . $this->job_id . "</li>
                </ul>";
            mail_error("Data missing in Class: vacancy Method: post_vacancy(). Validation error");
        }
    }
}