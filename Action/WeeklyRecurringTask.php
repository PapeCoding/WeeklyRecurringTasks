<?php
namespace Kanboard\Plugin\WeeklyRecurringTasks\Action;
use Kanboard\Action\Base;
use Kanboard\Model\TagModel;
use Kanboard\Model\TaskModel;
use Kanboard\Model\TaskTagModel;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\TaskProjectDuplicationModel;

/**
 * Automatically Clone Tasks with the DAILY/WEEKLY/BIWEEKLY/DAY-OF-WEEK-IN-CAPITAL tag.
 * 
 * DAY-OF-WEEK-IN-CAPITAL: MONDAY/TUESDAY/WEDNESDAY/THURSDAY/FRIDAY/SATURDAY/SUNDAY
 *
 * @package action
 * @author  Sebastian Pape, Sebastien Diot
 */
class WeeklyRecurringTask extends Base
{
    /**
     * Get automatic action description
     *
     * @access public
     * @return string
     */
    public function getDescription()
    {
        return t('Automatically clones Tasks with the DAILY/WEEKLY/BIWEEKLY or MONDAY/TUESDAY/WEDNESDAY/THURSDAY/FRIDAY/SATURDAY/SUNDAY tag');
    }
	
    /**
     * Get the list of compatible events
     *
     * @access public
     * @return array
     */
    public function getCompatibleEvents()
    {
        return array(
            TaskModel::EVENT_DAILY_CRONJOB,
        );
    }
	
    /**
     * Get the required parameter for the action (defined by the user)
     *
     * @access public
     * @return array
     */
    public function getActionRequiredParameters()
    {
        return array();
    }
	
    /**
     * Get the required parameter for the event
     *
     * @access public
     * @return string[]
     */
    public function getEventRequiredParameters()
    {
        return array();
    }
	
    /**
     * Check if the event data meet the action condition
     *
     * @access public
     * @param  array   $data   Event data dictionary
     * @return bool
     */
    public function hasRequiredCondition(array $data)
    {
        return true;
    }
	
    /**
     * Get currently due (yesterday to tomorrow) tasks query
     *
     * @access private
     * @param  integer  $project_id
     * @param  string   $tag
     * @return array
     */
    private function getDueTasks($project_id, $tag)
    {
		$tag_id = $this->tagModel->getIdByName($project_id, $tag);
        if ($tag_id == 0){return array();} /*  $tag not found in project */
		
        return $this->db->table(TaskModel::TABLE)
                    ->columns(
                        TaskModel::TABLE.'.id',
                        TaskModel::TABLE.'.project_id',
                        TaskModel::TABLE.'.date_due',
                        TaskModel::TABLE.'.title'
                    )
                    ->join(TaskTagModel::TABLE, 'task_id', 'id')
                    ->eq(TaskTagModel::TABLE.'.tag_id', $tag_id)
                    ->eq(TaskModel::TABLE.'.project_id', $project_id)
                    ->gte(TaskModel::TABLE.'.date_due', strtotime("today"))
                    ->lt(TaskModel::TABLE.'.date_due', strtotime("tomorrow"))
					->findAll();
    }
	
    /**
     * Process all relevant tasks of one project.
     *
     * @access private
     * @param  integer  $project_id
     * @param  string   $tag
     * @param  string   $delay
     * @return bool
     */
	private function processProject($project_id, $tag, $delay)
	{
		$result = true;
		$due_tasks = $this->getDueTasks($project_id, $tag);
		foreach ($due_tasks as $task) {
			$new_due_date = strtotime($delay, $task['date_due']);
			
			/* Check if task was already duplicated */
			if ($this->db->table(TaskModel::TABLE)->eq('project_id', $project_id)->eq('title', $task['title'])->eq('date_due', $new_due_date)->exists()) {
				continue; /* Nothing todo */
			}
			
			/* Duplicate task */
			$new_task_id = $this->taskProjectDuplicationModel->duplicateToProject($task['id'], $project_id);
			if ($new_task_id <= 0) {
				error_log('Failed to duplicate task: ID=' . $task['id'] . ', TITLE=' . $task['title'] . ', PROJECT=' . $project_id);
				$result = false;
				continue;
			}
			
			/* Update task with new due date */
			if (!$this->taskModificationModel->update(array('id' => $new_task_id, 'is_active' => 1, 'date_due' => $new_due_date))) {
				error_log('Failed to update duplicated task: ID=' . $new_task_id . ', TITLE=' . $task['title'] . ', PROJECT=' . $project_id);
				$result = false;
				continue;
			}
		}
		return $result;
	}

    /**
     * Execute the action
     *
     * @access public
     * @param  array   $data   Empty for this event
     * @return bool            True if all tasks could be processed correctly. False if one task could not be duplicated.
     */
    public function doAction(array $data)
    {
		$result = true;
		$today = strtoupper(date("l")); /* MONDAY/TUESDAY/WEDNESDAY/THURSDAY/FRIDAY/SATURDAY/SUNDAY */
		
		/* Loop over all active projects */
		foreach ($this->projectModel->getAllByStatus(ProjectModel::ACTIVE) as $project) {
			$result = $this->processProject($project['id'], "DAILY", "+1 day") && $result;
			$result = $this->processProject($project['id'], "WEEKLY", "+7 day") && $result;
			$result = $this->processProject($project['id'], "BIWEEKLY", "+14 day") && $result;
			$result = $this->processProject($project['id'], $today, "+7 day") && $result;
		}
		return $result;
    }
}
