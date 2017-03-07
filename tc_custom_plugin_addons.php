<?php
/*
   Plugin Name: TC custom plugin addons
   Plugin URI: http://www.amyjocarlson.com
   Description: Tranistion Coalition plugin is for their website purpose only and should not be given out the web.  
   There are two classes included in this file one for database connections used throughout the website and the other 
   is a widget class for bbpress formum called Join the discussion!
   
   Version: 1.2
   Author: Greg Carlson
   License: GPL2
   */
add_action('plugins_loaded', array('tc_template_master', 'load'), 200);
include_once('TinyMCE-shortcode-addon.php');
include_once('tc_forum_widget.php');
include_once('tc_coach_lern_widget.php');
include_once('tc_coach_comment_topiclist_widget.php');
//include_once('theme-my-login-custom.php');

if(!class_exists('tc_template_master')) {	
class tc_template_master {

	function load() {
	    $tc_template_master = new tc_template_master();
         function tc_getLERNcoach($courseid, $currentuserid){
		 global $wpdb;
		 $lerncoaches="";
		 $isCoach=0;
         $lerncoaches=$wpdb->get_var($wpdb->prepare("select coach_id from wp_wpcw_course_extras where course_id = %d",$courseid));
		 $aCoaches=explode(",",$lerncoaches);
	     for ($i=0;$i < count($aCoaches); $i++){
			 if ($aCoaches[$i] == $currentuserid){$isCoach++;}
		 }
		 return  $isCoach;
		 }
	    function tc_get_lern_course_enrollment($course_id){
		global $wpdb;
		$num_enrolled= $wpdb->get_var("Select count(*) from wp_wpcw_user_courses where course_id =" . $course_id);
		return $num_enrolled;
		}
	    function tc_get_lern_course_data(){
		global $wpdb;
		$lerndata =$wpdb->get_results($wpdb->prepare("select c.course_id, coach_id, course_title, course_desc, enrollment_key, start_date, max_enrolled, study_guide_path, course_logo_path from wp_wpcw_course_extras x, wp_wpcw_courses c where  x.course_id = c.course_id and course_type='%s'","LERN",OBJECT));
		return $lerndata;
		}
		function tc_portfolio_user_lern_get_review_ids($course_id){
		global $wpdb;
		//LERN units always follow the same order, we want engage, reflect, and network
         $lernreviewunits=$wpdb->get_results($wpdb->prepare("select unit_id from wp_wpcw_units_meta where parent_course_id = %d order by unit_order, unit_number LIMIT 1,3",$course_id, OBJECT));	
		 $reviewpagesIDs=array();
		 foreach ( $lernreviewunits as $item){
		 $reviewpagesIDs[] =  $item->unit_id;
		 }
         return   $reviewpagesIDs;		 
		}
        function tc_check_enrollment($user_id, $course_id){
		 global $wpdb;
		 $enrolled=$wpdb->get_var($wpdb->prepare("select user_id from wp_wpcw_user_courses where  user_id = %d and course_id = %d", $user_id, $course_id));
		 if (!enrolled){return 0;}
		 else
		 {return $enrolled;}
		 }
        //functions for portfolio module template 
		//get the name of module the user is on 
		function tc_portfolio_module_get_name($course_id){
		global $wpdb;
	         $module_name = $wpdb->get_var( $wpdb->prepare( 
			"   SELECT course_title 
				FROM wp_wpcw_courses
				WHERE course_id = %d
			", 
			$course_id) );
			return $module_name;
			}
		//get the total activities for the user	
		function tc_portfolio_module_get_activities($course_id){
		global $wpdb;
		$total_course_activities = $wpdb->get_var( $wpdb->prepare( 		
				"SELECT sum(meta_value)
				FROM 
				wp_wpcw_units_meta m,
				wp_postmeta p
				where 
				p.post_id=m.unit_id and
				meta_key='number_of_activities'
				and  parent_course_id = %d
				 and meta_value > 0",
				$course_id
			    ) );
		return $total_course_activities;
		}
		//get activities for the portfolio module
		function tc_portfolio_module_get_activity_answers($user_id, $course_id){
		global $wpdb;
		$activityRows = $wpdb->get_results($wpdb->prepare("select module_title,activity_id, post_id,activity_value,description from 
					wp_course_activities a, 
					wp_wpcw_user_progress u,
					wp_wpcw_units_meta m, 
					wp_wpcw_modules c
					where 
					u.user_id = a.user_id
					and
					u.unit_id = a.post_id
					and
					u.unit_id = m.unit_id
					and
					a.post_id = m.unit_id
					and
					m.parent_course_id and c.parent_course_id
					and 
					m.parent_module_id = c.module_id
					and 
					page_order > 0 
					and a.user_id = %d 
					and m.unit_id in (Select unit_id from wp_wpcw_units_meta where parent_course_id = %d) order by module_id", $user_id,$course_id), OBJECT ); 
					
				return $activityRows;
		}
        //get activities for the portfolio module
		function tc_portfolio_module_get_legacy_ppt($user_id, $course_id){
		global $wpdb;
		$pptest_legacy_rows = $wpdb->get_results($wpdb->prepare("Select * from tc_user_legacy_pptest where wp_user_id = %d and module_id = %d", $user_id,$course_id), OBJECT); 
		return $pptest_legacy_rows;
		}
		//get check box answers for portfolio module
		function tc_portfolio_module_get_check_boxes($course_id){
			global $wpdb;
			$check_boxes = $wpdb->get_results($wpdb->prepare(
			'select distinct(post_id) as post_id from wp_course_matrix where post_id in (Select unit_id from wp_wpcw_units_meta where parent_course_id =%d) 
			order by matrix_name', $course_id),OBJECT);
			return $check_boxes;
			}
		//return the last activity id the user was working on for this module. Returns nothing if module is all the way complete.
		function tc_portfolio_module_get_last_activity($user_id, $course_id){
			global $wpdb;
			$nextToCompleteID = $wpdb->get_var($wpdb->prepare("select min(unit_id) from wp_wpcw_units_meta where parent_course_id = %d and unit_id not in 
			(select unit_id from wp_wpcw_user_progress where user_id = %d and unit_id) AND unit_id not in (select parent_unit_id from wp_wpcw_quizzes)",$course_id, $user_id));
			return $nextToCompleteID;
		}
		//get the pre post test information about the module
		function tc_portfolio_module_get_prepost_test($user_id, $course_id, $v1ppt, $v1_ppt_unit_ids){
		global $wpdb;
		//check to see if they have prepost from v1 of the tests
        if ($v1ppt > 0 ){
		$rows = $wpdb->get_results ($wpdb->prepare("select quiz_title, quiz_type, quiz_correct_questions,quiz_question_total, quiz_completed_date, parent_unit_id, quiz_show_answers
                    from 
                    wp_wpcw_user_progress_quizzes p,
                    wp_wpcw_quizzes q
                    where q.quiz_id =p.quiz_id
                    and unit_id in (". $v1_ppt_unit_ids. ")
                    and user_id = %d
					and quiz_type <> %s
                    order by quiz_type",$user_id,'survey'),OBJECT);	
		}
		else{
		$rows = $wpdb->get_results ($wpdb->prepare("select quiz_title, quiz_type, quiz_correct_questions,quiz_question_total, quiz_completed_date, parent_unit_id, quiz_show_answers
                    from 
                    wp_wpcw_user_progress_quizzes p,
                    wp_wpcw_quizzes q
                    where q.quiz_id =p.quiz_id
                    and (q.parent_course_id = %d || q.former_course_id = %d)
                    and user_id = %d
					and quiz_type <> %s
                    order by quiz_type", $course_id, $course_id,$user_id,'survey'),OBJECT);
		}			
		return $rows;
		}
		//get the check box items per post and answers per user
		function tc_portfolio_module_get_check_box_selections($user_id, $post_id){
		global $wpdb;
		$userCheckboxSelections = $wpdb->get_row($wpdb->prepare("select * from wp_course_activities where post_id =%d and user_id=%d and description=%s",$post_id, $user_id,''));
		return $userCheckboxSelections;
		}
		//get the check box selections that user made 
		function tc_portfolio_module_get_check_box_answers($post_id){
		global $wpdb;
		$checklistItem = $wpdb->get_results($wpdb->prepare("select * from wp_course_matrix where post_id like (%d) and matrix_type=%s",$post_id, "checklist", OBJECT));
		return $checklistItem;
		}
		//get the total number of courses for this unit
		function tc_portfolio_module_get_course_units($course_id){
		global $wpdb;
		$courseUnits = $wpdb->get_var($wpdb->prepare("select count(*) from wp_wpcw_units_meta where parent_course_id =%d", $course_id));
		return $courseUnits;
		}
		//get the total number of courses that need to be completed_date
		function tc_portfolio_module_get_courses_to_complete($course_id, $user_id){
		global $wpdb;
		$notCompletedUnits = $wpdb->get_results($wpdb->prepare("select unit_id from wp_wpcw_units_meta where parent_course_id = %d and unit_id not in (Select unit_id from wp_wpcw_user_progress where user_id =%d)",$course_id, $user_id, OBJECT));
		return $notCompletedUnits;
		}
		//get the national courses
		 function tc_get_national_course_list(){
	     global $wpdb;
         $courseData=$wpdb->get_results($wpdb->prepare("select x.course_id, course_desc from wp_wpcw_course_extras x, wp_wpcw_courses w  where x.course_id = w.course_id and national_module in (1,2) order by w.course_id",OBJECT));
		 return $courseData;
         }
		 function tc_get_course_select_list($module_audience){
	     global $wpdb;
		 if ($module_audience=='MO'){
		 $courseData=$wpdb->get_results($wpdb->prepare("select course_id, course_logo_path from wp_wpcw_course_extras where course_type='learning module' and national_module in (0,2)",OBJECT));	 
		 }
		 else{
         $courseData=$wpdb->get_results($wpdb->prepare("select course_id, course_logo_path from wp_wpcw_course_extras where course_type='learning module' and national_module in (1,2)",OBJECT));
		 }
		return $courseData;
		 }
		//functions for Resources template
		//get resource simple links 
		function tc_resources_template_get_simple_links(){
		global $wpdb;
		$presentations_simple_links = $wpdb->get_results($wpdb->prepare("Select name, term_taxonomy_id, t.term_id from wp_terms t, wp_term_taxonomy x WHERE t.term_id = x.term_id AND x.parent = %d order by t.term_id", 27, OBJECT));
		return $presentations_simple_links;
		}
		//get post id for any resource links saved in user's pinned items (favorites)
		function tc_resources_template_get_pinned_resources($user_id, $post_id){
		global $wpdb;
		$find_post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM wp_favorites WHERE user_id =%d AND post_id = %d",$user_id,$post_id ));
		return $find_post_id;
		}
		//functions for tc presentation template
		//get presentation simple links
		function tc_tc_presentations_template_get_simple_links(){
		global $wpdb;
		$presentations_simple_links = $wpdb->get_results(
		$wpdb->prepare("Select name, term_taxonomy_id, t.term_id 
		from wp_terms t, wp_term_taxonomy x 
		WHERE t.term_id = x.term_id 
		AND x.parent = %d order by t.term_id",
		110,OBJECT)
		);
		return $presentations_simple_links;
		}
		//get post id for presentation links saved in user's pinned items (favorites)
		function tc_tc_presentations_template_get_pinned_presentations($user_id, $post_id){
		global $wpdb;
		 $find_post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM wp_favorites WHERE user_id =%d AND post_id = %d", $user_id,$post_id));
		 return $find_post_id;
		}
		//functions for TC materials template
		//get child posts for tc materials
		function tc_materials_template_get_child_posts($post_id){
		global $wpdb;
		$child_posts = $wpdb->get_results($wpdb->prepare("select guid, post_title, ID from wp_posts where ID in (select post_id from wp_postmeta  where meta_key=%s and post_id = %d)",'upload_pdf_resource', $post_id, OBJECT));
		return $child_posts;
		}
	     //functions for the Webinars template
		//get child posts that belong to webinar post
		function tc_webinars_get_child_posts($post_id){
		global $wpdb; 
		$child_posts = $wpdb->get_results($wpdb->prepare("select distinct(guid), post_title, m.post_id from wp_posts p, wp_postmeta m where p.ID = m.post_id  and ID in (select meta_value from wp_postmeta where post_id  = %d and meta_key=%s)", $post_id,'additional_resources',
			OBJECT));
		return $child_posts;
		}
		//functions for the PDhub roster listing template and PDhub student progress 
		//get all users that are a part of the PD hub group
		function tc_pdhub_get_users_group(){
			global $wpdb;												
			$pd_hub_user_group = $wpdb->get_results($wpdb->prepare("SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id = %d ORDER BY `object_id` ASC",38, OBJECT));
			return $pd_hub_user_group;
		}
		//get all users that have at least one roster. They should be roster leaders. This is called for admin users.
		function tc_pdhub_get_roster_leaders($pd_hub_user_id){
			global $wpdb;
			$user_rosters = $wpdb->get_results($wpdb->prepare("select t.term_id, term_order, name FROM
								wp_term_taxonomy t, wp_term_relationships w, wp_terms x 
								WHERE
								w.object_id = %d and 
								t.term_taxonomy_id = w.term_taxonomy_id
								AND
								t.term_id= x.term_id
								and w.term_order = %d
								and taxonomy =%s order by term_id desc" ,$pd_hub_user_id,1, 'user-group', OBJECT));
			return $user_rosters;
		}
		//get all rosters for the roster leader 
		function tc_pdhub_get_rosters_for_roster_leader($roster_leader){
			global $wpdb;
			$user_rosters = $wpdb->get_results(
			$wpdb->prepare("select t.term_id,term_order, name 
			FROM
			wp_term_taxonomy t, wp_term_relationships w, wp_terms x 
			WHERE
			w.object_id =%d and 
			t.term_taxonomy_id = w.term_taxonomy_id
			AND
			t.term_id= x.term_id
			and t.term_id not in (Select term_id from wp_terms where term_group = %d ) 
			and taxonomy = %s order by term_id desc", 
			$roster_leader,1,'user-group', 
			OBJECT));
			return $user_rosters;
		}
		
		//functions for PD hub Manage Roster template
		//get users roles for roster based on the term_taxonomy_id
		function tc_pdhub_manage_roster_get_roles($term_taxonomy_id){
			global $wpdb;
			$roster_roles = $wpdb->get_results($wpdb->prepare("Select object_id, term_order from wp_term_relationships 
			where term_order in (0,1,2,3,4,5)  
			and term_taxonomy_id =%d 
			order by term_order" ,
			$term_taxonomy_id, 
			OBJECT));
			return $roster_roles;
		}
		//functions for PDhub student QI progress
		//get course_ids for roster_students
		function tc_pdhub_student_progress_qi_surveys($user_id, $quiz_id){
			global $wpdb;
			$surveys=$wpdb->get_results($wpdb->prepare('Select statistic_ref_id, FROM_UNIXTIME(create_time) as createdtime from wp_wp_pro_quiz_statistic_ref where user_id=%d and quiz_id=%d order by statistic_ref_id desc limit 0,1',$user_id,$quiz_id, OBJECT));
           
			if ($wpdb->num_rows <= 0){
			return "none";
			}
			else{
			return $surveys;
			}
		}
		//functions for PDhub student progress
		//get the number of members for an individual roster
		function tc_pdhub_student_progress_get_num_members($term_taxonomy_id){
			global $wpdb;
			$num_member = $wpdb->get_var($wpdb->prepare("SELECT count(term_order) FROM wp_term_relationships WHERE term_taxonomy_id = %d",$term_taxonomy_id, OBJECT));
			return $num_member;
		}
		//get course_ids for roster_students
		function tc_pdhub_student_progress_get_course_ids($term_taxonomy_id){
			global $wpdb;
			$modules = $wpdb->get_results($wpdb->prepare("select distinct(c.course_id) 
			from wp_wpcw_courses c, 
			wp_wpcw_units_meta m, 
			wp_wpcw_user_progress p, 
			wp_wpcw_course_extras x
			where c.course_id = m.parent_course_id
			and
			m.unit_id = p.unit_id
			and c.course_id = x.course_id
			and p.user_id in (select object_id from wp_term_relationships where term_taxonomy_id = %d ) 
			and x.course_type =%s order by c.course_id ASC",
			$term_taxonomy_id, 'learning module', OBJECT));
			return $modules;
		}
		//get the course extra fields for each module
		function tc_pdhub_student_progress_course_extras($course_a){
			global $wpdb;
			$query = "select course_logo_path from wp_wpcw_course_extras where course_id in(".implode(',',$course_a).") order by course_id ASC";
			$courseExtraRow=$wpdb->get_results($wpdb->prepare($query, OBJECT));
			return $courseExtraRow;
		}
		//get student roster member pre post test progress data for each module in table cell
		function tc_pdhub_student_progress_get_module_progress($term_taxonomy_id){
			global $wpdb;
			$roster = $wpdb->get_results(
			$wpdb->prepare("Select object_id, term_order from wp_term_relationships 
			where term_order in (3, 4) 
			and term_taxonomy_id =%d order by term_order" ,
			$term_taxonomy_id , 
			OBJECT)
			);
			return $roster;
		}
		//get students that have not started the module
		function tc_pdhub_get_modules_not_started_data($course_ids, $term_taxonomy_id){
			global $wpdb;
			$roster = $wpdb->get_results(
			$wpdb->prepare("Select object_id from wp_term_relationships r, wp_wpcw_user_courses c
							where 
							r.object_id = c.user_id
							and
							term_order in (3, 4) 
							and c.course_id in (". $course_ids.")
							and course_progress = 0
                            and term_taxonomy_id =%d order by term_order",$term_taxonomy_id , OBJECT));
			return $roster;
		}
		//get all the modules and users when no one has started yet (progress is 0)
		function tc_pdhub_get_roster_0_percent_by_group($course_ids, $term_taxonomy_id){
			global $wpdb;
			$roster = $wpdb->get_results(
			$wpdb->prepare("Select object_id, term_order,course_title,c.course_id, course_progress from wp_term_relationships r, wp_wpcw_user_courses c,
						   wp_wpcw_courses w where 
							r.object_id = c.user_id
							and w.course_id = c.course_id
							and
							term_order in (3, 4) 
							and c.course_id in (". $course_ids.")
							and course_progress = 0
							and term_taxonomy_id =%d order by c.course_id",$term_taxonomy_id , OBJECT));
			return $roster;
		}
		//get the quiz and post test popup data for all users on all courses selected
		function tc_pdhub_get_roster_summary_data_by_group($course_ids, $roster_id){
		global $wpdb;
		//first get the unit ids for the selected modules
		$sql="select distinct(unit_id) from  wp_wpcw_user_progress_quizzes p,  wp_wpcw_quizzes q  where q.quiz_id = p.quiz_id 
			 and (q.parent_course_id in (".$course_ids.") || q.former_course_id in (". $course_ids.")) and quiz_title NOT LIKE '%survey%'";
		     $unitrows= $wpdb->get_results($sql, OBJECT);
				foreach ($unitrows as $item){
				$unitids.=$item->unit_id .",";
				}
				$unitids=substr_replace($unitids ,"",-1);
		        $sql ="select  q.quiz_id, p.user_id,quiz_title, parent_course_id, former_course_id,course_title,
									quiz_correct_questions,quiz_question_total, quiz_completed_date as completed_date, parent_unit_id ,course_progress,quiz_data 
									from wp_wpcw_user_progress_quizzes p, 
									wp_wpcw_quizzes q, 
									wp_wpcw_user_courses c,
									wp_wpcw_courses w 
									where q.quiz_id =p.quiz_id and 
									(c.course_id = q.parent_course_id ||   c.course_id = former_course_id )
									and 
									(c.course_id = w.course_id || c.course_id = former_course_id )
									AND
									(q.parent_course_id = w.course_id || q.former_course_id = w.course_id)
									and c.user_id = p.user_id 
                                    and unit_id in (". $unitids.")
									and p.user_id in (select object_id from wp_term_relationships where term_taxonomy_id in (select term_taxonomy_id from wp_term_taxonomy where term_id = ". $roster_id."))
								    and quiz_title NOT LIKE '%survey%'
                                    order by parent_course_id, former_course_id";
			$rows= $wpdb->get_results($sql, OBJECT);
			return $rows;
			}
		//get pdhub quiz summary for student member on roster for each module
		function tc_pdhub_student_progress_get_student_quiz_summary($course_id, $student_id){
			global $wpdb;
			$sql = $wpdb->prepare("select quiz_title, parent_course_id, quiz_type, quiz_correct_questions,quiz_question_total, quiz_completed_date as completed_date, parent_unit_id, quiz_show_answers
										from wp_wpcw_user_progress_quizzes p,
										wp_wpcw_quizzes q
										where q.quiz_id =p.quiz_id
										and parent_course_id =%d
										 and user_id = %d
										 and quiz_title NOT LIKE '%s'
										order by parent_course_id", $course_id, $student_id,'%survey%', OBJECT);
			$rows= $wpdb->get_results($sql);
			$numQuizzes = $wpdb->num_rows;
			$i=0;
			$quiz_summary.=$numQuizzes;
			foreach ($rows as $item){
				//get the post quiz details
				$quizDetails = WPCW_quizzes_getAssociatedQuizForUnit($item->parent_unit_id);
				$quizProgress = WPCW_quizzes_getUserResultsForQuiz($student_id, $item->parent_unit_id, $quizDetails->quiz_id);
				//complete date will be true if post test has been completed
				if($item->completed_date <> ""){
				$date = date_create($item->completed_date);
				$complete_date = date_format($date, "m/d/Y");
				}
				// User has completed the test ... show the results.
				if ($quizProgress)  
				{
					$postTestBox = WPCW_quizzes_showAllCorrectAnswers($quizDetails, $quizProgress);
				}
				
				if ($numQuizzes == 1) { //only pretest
					$quiz_summary.="Pre-test: ". $item->quiz_correct_questions ."/". $item->quiz_question_total;
				}
				elseif ($numQuizzes == 2) { //pre and post
						if ($i == 0){
							$quiz_summary.="<b>Pre-test:</b> ". $item->quiz_correct_questions ."/". $item->quiz_question_total;
						}
						else{
							$quiz_summary.="<br><b>Post-test:</b> ". $item->quiz_correct_questions ."/". $item->quiz_question_total ."<br>Taken on: ".$complete_date.
							"<br><span class=student_progress_link><a class=fancybox href=\"#fancypopup_".$student_id."\">Review post test</span></a></span>
							<div class=fancybox-hidden><div id=fancypopup_".$student_id." style='width: 800px; height: 1100px;'>".$postTestBox."</div></div>
							<br class='none' /><span class=student_progress_link><a class='print_summary_sheet' href='#' data-courseid='".$item->parent_course_id."' data-userid='".$student_id."'>View student summary</a></span>";
						}
						$i++;
					}
              				
			}	
			return $quiz_summary;		
		}
		//get student progress for the module 
		function tc_pdhub_student_progress_get_student_progress($course_id, $student_id){
			global $wpdb;
			$student_module_complete = $wpdb->get_var($wpdb->prepare("SELECT  count(p.unit_id) from wp_wpcw_user_progress p, wp_wpcw_units_meta m
												where p.unit_id = m.unit_id AND
												m.parent_course_id = %d 
												and user_id =%d",$course_id,$student_id));	
			$module_total = $wpdb->get_var($wpdb->prepare("Select count(*) from wp_wpcw_units_meta where parent_course_id =%d",$course_id));
			
			$student_progress = ($student_module_complete/$module_total) * 100;
			$student_progress = number_format($student_progress, 0, '.', '');
			return $student_progress;
		}
		
		//functions for action plan template
		//get module name based on course id
		function tc_action_plan_get_module_name($courseID){
			global $wpdb;
			$moduleName = $wpdb->get_var($wpdb->prepare("Select course_title from wp_wpcw_courses where course_id =%d", $courseID, OBJECT));
			return $moduleName;
		}
		//get course activity text area answers
		function tc_action_plan_get_course_activity_answers($user_id, $postsToCheck,$activity_str){
			global $wpdb;
			$query = "select post_id,activity_value, description from wp_course_activities where user_id = ".$user_id." and post_id in(".implode(',',$postsToCheck).")";
			$activityRows = $wpdb->get_results($wpdb->prepare($query." and description like '%s' order by post_id",$activity_str, OBJECT));
			return $activityRows;
		}
		
		//functions for portfolio user module list tempalte
		 //get the course extra fields
		 function tc_portfolio_user_module_list_get_course_extra_fields($course_id){
			global $wpdb;
			$courseExtraRow=$wpdb->get_row("select * from wp_wpcw_course_extras where course_id=". $course_id);
			return $courseExtraRow;
			}
		  //get the last item that the user was working on.
		 function tc_portfolio_user_module_list_get_activity($user_id, $course_id){
			global $wpdb;
			$nextToCompleteID = $wpdb->get_var("select min(unit_id) from wp_wpcw_units_meta where parent_course_id = ".$course_id." 
			and unit_id not in (select unit_id from wp_wpcw_user_progress where user_id = ". $user_id.") AND unit_id not in (select parent_unit_id from wp_wpcw_quizzes)");
			return $nextToCompleteID;
		 }
		 //get legacy course modules 
		function tc_portfolio_user_moduel_list_get_legacy_modules($user_id){
			global $wpdb;
			$user_legacy_course_data = $wpdb->get_results($wpdb->prepare("SELECT u.tc_module_id, m.module_logo, module_name, wp_user_id FROM tc_legacy_modules m, 
			tc_user_legacy_modules u WHERE m.tc_module_id = u.tc_module_id AND wp_user_id = %d", $user_id, OBJECT));
			return $user_legacy_course_data;
		}
		
		//functions for legacy porfolio module template
		//get test scores for module based on module id and user id
		function tc_legacy_portfolio_module_template_get_tests($user_id, $module_id){
			global $wpdb;
			$module_data = $wpdb->get_results($wpdb->prepare("SELECT pre_test_score, post_test_score, pre_test_date, post_test_date, module_name FROM tc_user_legacy_modules u, 
			tc_legacy_modules l WHERE u.tc_module_id = %d and l.tc_module_id = %d AND wp_user_id = %d", $module_id, $module_id, $user_id, OBJECT));
			return $module_data;
		}
		//get the number of activities the user has completed for the module
		function tc_legacy_portfolio_module_template_user_activities($user_id, $course_id){
			global $wpdb;
			$num_activities = $wpdb->get_var("SELECT count(*) FROM tc_module_activity_answers a, tc_module_activity_questions q, user_link l 
											WHERE l.user_id = a.user_id 
											AND l.wp_user_id = ".$user_id." AND q.module_id = ".$course_id."
											AND q.active = 1 AND a.module_activity_questions_id = q.module_activity_questions_id");
			return $num_activities;
		}
		//get the total activities for the module
		function tc_legacy_portfolio_module_template_total_activities($course_id){
			global $wpdb;
			$total_activities = $wpdb->get_var("SELECT count(*) FROM `tc_module_activity_questions` where module_id = ".$course_id." AND active = 1");
			return $total_activities;
		}
		//get the questions and answers for activities on module for the user	    
		function tc_legacy_portfolio_module_template_questions_answers($user_id, $course_id){
			global $wpdb;
			$activity_answers = $wpdb->get_results($wpdb->prepare("SELECT q.questions, a.answer, q.ss_question_session  FROM tc_module_activity_answers a, tc_module_activity_questions q, user_link l
			WHERE l.user_id = a.user_id AND l.wp_user_id = %d AND q.module_id = %d AND q.active = 1 AND a.module_activity_questions_id = q.module_activity_questions_id ORDER BY q.ss_question_order ASC", $user_id, $course_id, OBJECT));
			return $activity_answers;
		}
		 
		 //functions for Learning module library template
		 //get category ids for the learning module
		 function tc_learning_module_library_get_catergories($category_a){
			global $wpdb; 
			$queryStr = " AND x.term_id in (".implode(',',$category_a).") order by t.term_id";
			$parent_categories = $wpdb->get_results($wpdb->prepare("SELECT x.term_id from wp_terms t , wp_term_taxonomy x 
					Where x.term_id = t.term_id 
					AND slug LIKE '%s' 
					AND taxonomy = 'simple_link_category'".$queryStr."", "%librarymodule%", OBJECT));
			 return $parent_categories;
		}
		
		//get child categories for parent id of module
		function tc_learning_module_library_get_child_categories($parent_id){
			global $wpdb;
			$child_categories = $wpdb->get_results($wpdb->prepare("SELECT term_id, term_taxonomy_id FROM wp_term_taxonomy WHERE parent = %s",$parent_id, OBJECT));
			return $child_categories;
		}
		//get post id if post is a part of user's favorites
		function tc_learning_module_library_get_user_favorites($current_id, $post_id){
			global $wpdb;
			$queryStr = "SELECT post_id FROM wp_favorites WHERE user_id = ".$current_id." AND post_id = ".$posts[$i];
			$get_post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM wp_favorites WHERE user_id = %d AND post_id = %d", $current_id, $post_id, OBJECT));
			return $get_post_id;
		}
		
		//functions for favorites template
		// get saved news items favorites
		function tc_favorites_get_news_items($user_id){
		global $wpdb;
		$fav_news_items = $wpdb->get_results($wpdb->prepare("Select p.post_type, p.id, p.post_title, f.user_id, f.post_id, f.favorite_id, f.entry_date FROM wp_posts p, wp_favorites f WHERE p.post_type = 'post' 
		AND p.id=f.post_id AND f.user_id = %d AND p.id in (select object_id from wp_term_relationships where term_taxonomy_id = 39)", $user_id, OBJECT));
		return $fav_news_items;
		}
		//get saved TC items favorites
		function tc_favorites_get_tc_items($user_id){
		global $wpdb;
		$fav_tc_items = $wpdb->get_results($wpdb->prepare("Select p.post_type, p.id, p.post_title, f.user_id, f.post_id, f.entry_date, f.favorite_id FROM wp_posts p, wp_favorites f 
		WHERE (p.post_type = 'post' OR p.post_type = 'assessment_review' OR p.post_type = 'tc_materials' OR p.post_type = 'tip' OR p.post_type = 'webinar')
		AND p.id=f.post_id AND f.user_id = %d AND p.id not in (select object_id from wp_term_relationships where term_taxonomy_id = 39)", $user_id, OBJECT));
		return $fav_tc_items;
		}
		//get saved learning module items
		function tc_favorites_get_learning_module_items($user_id){
			global $wpdb;
			$course_units = $wpdb->get_results($wpdb->prepare("Select p.post_type, p.id, p.post_title, f.user_id, f.post_id, f.entry_date, f.favorite_id FROM wp_posts p, wp_favorites f 
			WHERE p.post_type = 'course_unit' AND p.id=f.post_id AND f.user_id = %d", $user_id, OBJECT));
			return $course_units;
		}
		//get saved resource items 
		function tc_favorites_get_resource_items($user_id){
		global $wpdb;
		//Query to retrieve saved Resource Links favorite
		$resource_links = $wpdb->get_results($wpdb->prepare("Select p.post_type, p.id, p.post_title, f.user_id, f.post_id, f.entry_date, f.favorite_id FROM wp_posts p, wp_favorites f 
		WHERE p.post_type = 'simple_link' AND p.id=f.post_id AND f.user_id = %d", $user_id, OBJECT));
		return $resource_links;
		}
		//get saved discussion items
		function tc_favorites_get_discussion_items($user_id){
		global $wpdb;
		$discussion_links = $wpdb->get_results($wpdb->prepare("Select p.post_type, p.id, p.post_title, f.user_id, f.post_id, f.entry_date, f.favorite_id FROM wp_posts p, 
		wp_favorites f WHERE (p.post_type = 'forum' OR p.post_type = 'topic') AND p.id=f.post_id AND f.user_id = %d", $user_id, OBJECT));
		return $discussion_links;
		}
               function	 tc_reviews_get_number_reviews_per_post($post_id){
	        global $wpdb;
		$num_reviews = $wpdb->get_var($wpdb->prepare("select meta_value from wp_postmeta where  meta_key = 'crfp-total-ratings' and post_id = %d", $post_id, OBJECT));
		return $num_reviews;
               }
		function get_meta_value_by_key($post_id, $key){
		global $wpdb;
		$meta_value = $wpdb->get_var($wpdb->prepare("select meta_value from wp_postmeta where post_id =%d and meta_key='%s'", $post_id,$key, OBJECT));
		return $meta_value;
		}

		function  get_multiple_meta_values_by_key($post_id, $meta_key){
		global $wpdb;
		$meta_values = $wpdb->get_results($wpdb->prepare("select meta_value from wp_postmeta where post_id =%d and meta_key='%s'", $post_id,$meta_key, OBJECT));
		return $meta_values;
		}
		
		function createStateSelectList($selected){
		global $wpdb;
		$s="";
		$stateInfo = $wpdb->get_results("Select * from states order by state_id ",OBJECT);
		$s.="<option value=''>Select</option>";
		foreach ($stateInfo as $info){
		$s.="<option value='". $info->abbreviation ."'";
			if ($info->abbreviation == $selected){ $s.=" selected ";}
	        $s.=">". $info->state."</option>";
		}
	        return $s;
		}
		
		function createTCroleList($selected){
		global $wpdb;
		$s="";
		$roleInfo = $wpdb->get_results("Select * from tc_roles order by role ",OBJECT);
		$s.="<option value=''>Select</option>";
		foreach ($roleInfo as $info){
		$s.="<option value='". $info->role ."'";
			if ($info->role == $selected){ $s.=" selected ";}
	        $s.=">". $info->role."</option>";
		}
	        return $s;
		}
	function tc_portfolio_module_get_implementation_plan($userID, $courseID){
	   global $wpdb;
       $user_info = get_userdata($userID);
       $displayName = $user_info->first_name . " " . $user_info->last_name;
       $displayEmail = $user_info->user_email;
       $logoPath="<img src='/wp-content/uploads/2014/12/tc_logo.png'>";
       $modelCourseIDs=array('2','8192','1','8205','2','8260','2','8216','1','8225','1','8239');
       $otherCourseIDs=array('2','8255','1','8365','2','8365');
			//header and logo
			$heading="<br><br><br><br><p style='text-align: center;'><span style='font-size: 12px;'>Report generated by ". $displayName." (". $displayEmail.") on" .date('y-m-d hh:mm:ss')."</span><br></p>
			<p style='text-align: center;'><img class='aligncenter size-full wp-image-8389' src='/wp-content/uploads/2014/12/tc_logo.png' alt='tc_logo' width='195' height='85' /></p>
			<br><p style='text-align: center;'><span style='font-size: 16px;'><strong>The <em>Essentials of Self-Determination</em> Training Module</strong></span></p>
			<p style='text-align: center;'><br>www.transitioncoalition.org<br><strong>Reflection & Implementation Plan</strong><br><br></p>";
           $table="<center><table class=basic_table><tr><td><strong>Key Element from <i>Model of Self-Determination</i> Field & Hoffman (2005)</strong> </td><td>Your responses in Session 2 identifying additional content you need to address with your students in each element</td></tr>";
		   for ($i = 0; $i < count($modelCourseIDs); $i+=2){
				$order= $modelCourseIDs[$i];
				$postTitle =  get_the_title( $modelCourseIDs[$i+1] );
				$post_id = $modelCourseIDs[$i+1];
				$answer= $wpdb->get_var($wpdb->prepare("Select activity_value from wp_course_activities where user_id=%d and page_order=%d and  post_id = %d",$userID, $order, $post_id));
					if ($answer ==""){
					$answer="<i>NOT ANSWERED YET</i>";
					}
					$table .="<tr><td><strong>". $postTitle."</strong></td><td>". $answer."</td></tr>";
					}
				$table .="</table><br><br>";
				for ($i = 0; $i < count($otherCourseIDs); $i+=2){
					$order=$otherCourseIDs[$i];
					$post_id =$otherCourseIDs[$i+1];
					$rows= $wpdb->get_results($wpdb->prepare("Select activity_value, post_id, description from wp_course_activities where user_id=%d and page_order =%d and post_id =%d",$userID, $order, $post_id,OBJECT)); 
							foreach ($rows as $row){
							$summaryAnswer= $row->activity_value;
							$summaryQuestion= "Your response to: " . $row->description;
							$final .="<div style='text-align:left'><p><strong>". $summaryQuestion ."</strong></p><p>". $summaryAnswer ."<br><br></p></div>";
							}
					}
		   $content = "<div style='width:800px'; >". $heading . $table . $final ."</center></div>";	
		 return $content;
	     }
	}	
}//end class	
}//end if statement
?>