<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Convert_tracked_time extends CI_Migration {

    public function up()
	{
		$tasks = $this->db->get('project_tasks')->result();
		
		foreach ($tasks as $task)
		{
			if ($task->hours == 0) continue;
			
			$start_date = new DateTime;
			
			$hours = $task->hours;
			
			if ($task->hours > 23)
			{
				$days = floor($task->hours / 24);
				$start_date->modify('-'.$days.' days');
				
				// Get it down to number of hours this day
				$hours -= ($days * 24);
			}
			
			$int_hours = (int) $hours;
			$minutes = ($hours - $int_hours) * 60;
			
			$hours > 0 && $start_date->modify('-'.$int_hours.' hours');
			$minutes > 0 && $start_date->modify('-'.$minutes.' minutes');
			
			$this->db->insert('project_times', array(
				'project_id' => $task->project_id,
				'task_id' => $task->id,
				'start_time' => $start_date->format('H:i'),
				'minutes' => $task->hours * 60,
				'end_time' => date('H:i'),
				'date' => $start_date->format('U'),
				'user_id' => 1,
			));
		}
    }

    public function down()
	{
	    $this->db
			->where('slug', 'email_new_proposal')
			->delete('settings');
	}
}