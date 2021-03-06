<?php defined('BASEPATH') or exit('No direct script access allowed');

class Projects extends PROJECTS_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Employee_model');
        $this->load->model('Project_model');
    }

    public function index()
    {
        $this->data['page'] = 'projects/projects';
        
        //Get all incomplete.
        $this->data['projects'] = $this->Project_model->get_all_incomplete();
        
        //Get customers dropdown.
        $this->load->model('Customer_model');
        $this->data['customers'] = $this->Customer_model->get_dropdown();

        $this->load->view('template', $this->data);
    }

    public function all()
    {
        $this->data['page'] = 'projects/projects';
        
        //Get all projects.
        $this->data['projects'] = $this->Project_model->get_all();
        
        //Get customers dropdown.
        $this->load->model('Customer_model');
        $this->data['customers'] = $this->Customer_model->get_dropdown();

        $this->load->view('template', $this->data);
    }

    public function complete()
    {
        $this->data['page'] = 'projects/projects';
        
        //Get all complete.
        $this->data['projects'] = $this->Project_model->get_all_complete();
        
        //Get customers dropdown.
        $this->load->model('Customer_model');
        $this->data['customers'] = $this->Customer_model->get_dropdown();

        $this->load->view('template', $this->data);
    }

    public function archive()
    {
        $this->data['page'] = 'projects/projects';
        
        //Get all archived.
        $this->data['projects'] = $this->Project_model->get_all_archived();
        
        //Get customers dropdown.
        $this->load->model('Customer_model');
        $this->data['customers'] = $this->Customer_model->get_dropdown();

        $this->load->view('template', $this->data);
    }

    public function form($id = '')
    {
        $this->data['page'] = 'projects/projects_form';
        
        //Get a list of project statuses for the dropdown.
        $this->data['project_type'] = array(
            '' => '',
            'Q' => 'Quote',
            'P' => 'Project',
            'R' => 'Request',
        );
        
        //Get customers dropdown.
        $this->load->model('Customer_model');
        $this->data['customers'] = $this->Customer_model->get_dropdown_live();
        
        //Get departments.
        $this->load->model('Department_model');
        $this->data['departments'] = $this->Department_model->get_all();
        
        //Get employees.
        $this->load->model('Employee_model');
        $this->data['employees'] = $this->Employee_model->get_all_except_sales();
        $this->data['employees_dropdown'] = $this->Employee_model->get_dropdown();
        
        //If an id is set, get record in order to populate the screen.
        if ($id != '') {
            if (!empty($result = $this->Project_model->get($id))) {
                $this->populate_screen($result);
            }
            
            //And get associated departments.
            if (!empty($result = $this->Project_model->get_departments($id))) {
                $departments = array();
                foreach ($result as $row) {
                    //Mark any departments in the result-set as CHECKED.
                    $departments['department[' . $row['department_id'] . ']'] = 'CHECKED';
                }

                $this->populate_screen($departments);
            }
            
            //And get associated employees.
            if (!empty($result = $this->Project_model->get_employees($id))) {
                $employees = array();
                foreach ($result as $row) {
                    //Mark any departments in the result-set as CHECKED.
                    $employees['employee[' . $row['employee_id'] . ']'] = 'CHECKED';
                }

                $this->populate_screen($employees);
            }
        }

        $this->load->view('template', $this->data);
    }

    public function view($id)
    {
        $this->data['page'] = 'projects/projects_view';
        
        //Get project information.
        $this->data['project'] = $this->Project_model->get($id);
        $this->data['project_status'] = $this->data['project']['project_status'];
        switch ($this->data['project_status']) {
            case "C":
                $this->set_message('This project was COMPLETED on ' . $this->format->date($this->data['project']['project_completed_date']) . '!', 'success');
                break;
            case "A":
                $this->set_message('This project was ARCHIVED on ' . $this->format->date($this->data['project']['project_archived_date']) . '!', 'warning');
                break;
            case "I":
            default:
                $this->set_message('This project is INCOMPLETE!', 'danger');
                break;
        }
        
        //Get project departments.
        $this->data['departments'] = $this->Project_model->get_departments($id);
        
        //Get project employees.
        $this->data['employees'] = $this->Project_model->get_employees($id);
        
        //Get project customer.
        $this->data['customer'] = $this->Project_model->get_customer($id);
        
        //Get project tasks.
        $this->data['tasks'] = $this->Project_model->get_tasks($id);
        
        //Get project files.
        $this->data['files'] = $this->Project_model->get_files($id);

        //Get a list of the project's notes
        $this->data['notes'] = $this->Project_model->get_notes($id);

        //Get list of employees
        $this->data['employees'] = $this->Employee_model->get_all_except_sales();

        //Get a list of the customer's reminders
        $this->data['reminders'] = $this->Project_model->get_reminders($id);

        //Get history
        $this->data['history'] = $this->Project_model->get_history($id);

        $this->load->view('template', $this->data);
    }

    public function delete_note($project_id, $note_id)
    {
        $this->Project_model->delete_note($project_id, $note_id);

        $this->set_message('Note deleted successfully', 'danger');
        redirect('projects/view/' . $project_id . '/#project-notes-tab');
    }

    public function delete_reminder($project_id, $reminder_id)
    {
        $this->Project_model->delete_reminder($project_id, $reminder_id);

        $this->set_message('Reminder deleted successfully', 'danger');
        redirect('projects/view/' . $project_id . '/#project-reminders-tab');
    }

    public function validate()
    {
        $pass = $this->get_validations();

        return $pass;
    }

    public function action()
    {
        //Retrieve the record's id if it exists in the form.
        $id = $this->input->post('project_id');
        
        switch ($this->action) {
            case "save":
                //Validate the form submission.
                if ($this->validate()) {
                    $this->Project_model->save($id);
                    $this->set_message('Project information saved successfully', 'success');

                    if ($id != '') {
                        redirect('projects/view/' . $id);
                    } else {
                        redirect('projects');
                    }
                }
                
                //The validation failed so retrieve POST data and reload the page.
                $this->populate_screen($this->input->post());
                redirect('projects/form/' . $id);
                break;
            case "save note":
                if ($this->validate()) {
                    $this->Project_model->save_note($id);
                    $this->set_message('Note saved successfully.', 'success');
                }
                redirect('projects/view/' . $id . '/#project-notes-tab');
                break;
            case "save task":
                if ($this->validate()) {
                    $task_id = $this->input->post('project_task_id');
                    $this->Project_model->save_task($task_id);
                    $this->set_message('Task saved successfully.', 'success');
                }
                echo json_encode($this->session->userdata('projects_messages'));
                exit;
                break;
            case "save reminder":
                if ($this->validate()) {
                    $this->Project_model->add_reminder($id);
                    $this->set_message('Reminder saved successfully.', 'success');
                }
                echo json_encode($this->session->userdata('projects_messages'));
                exit;
                break;
            case "add file":
                //Upload the file to the server.
                if ($upload_data = $this->upload($id)) {
                    //Save the file info in the database.
                    $this->Project_model->upload($id, $upload_data);
                }
                redirect('projects/view/' . $id . '/#project-files-tab');
                break;
            case "approve project":
                $this->Project_model->approve_project($id);
                $this->set_message('This project is now approved!', 'success');
                redirect('projects/view/' . $id);
                break;
            case "complete project":
                $this->Project_model->complete_project($id);
                $this->set_message('This project is now complete!', 'success');
                redirect('projects/view/' . $id);
                break;
            case "incomplete project":
                $this->Project_model->incomplete_project($id);
                $this->set_message('This project is now incomplete!', 'danger');
                redirect('projects/view/' . $id);
                break;
            case "archive project":
                $this->Project_model->archive_project($id);
                $this->set_message('This project is now archived!', 'warning');
                redirect('projects/view/' . $id);
                break;
            case "restore project":
                $this->Project_model->restore_project($id);
                $this->set_message('This project has been restored!', 'success');
                redirect('projects/view/' . $id);
                break;
            case "add reminder":
                if ($this->validate()) {
                    $this->Project_model->add_reminder($id);
                    $this->set_message('Reminder added successfully.', 'success');
                }
                redirect('projects/view/' . $id . '/#project-reminders-tab');
                break;
            case "clear task":
                redirect('projects/view/' . $id . '/#project-tasks-tab');
                break;
            case "clear reminder":
                redirect('projects/view/' . $id . '/#project-reminders-tab');
                break;
            case "clear note":
                redirect('projects/view/' . $id . '/#project-notes-tab');
                break;
        }
    }

    public function edit_task($task_id)
    {
        $task = $this->Project_model->get_task($task_id);
        $this->populate_screen($task);
    }

    public function complete_task($project_id, $task_id)
    {
        $this->Project_model->complete_task($project_id, $task_id);
        $this->set_message('Task is now complete!', 'success');
        redirect('projects/view/' . $project_id . '/#project-tasks-tab');
    }

    public function delete_task($project_id, $task_id)
    {
        $this->Project_model->delete_task($project_id, $task_id);
        $this->set_message('Task has been deleted!', 'danger');
        redirect('projects/view/' . $project_id . '/#project-tasks-tab');
    }

    public function delete_file($project_id, $file_id)
    {
        $this->Project_model->delete_file($project_id, $file_id);
        $this->set_message('File has been deleted!', 'danger');
        redirect('projects/view/' . $project_id . '/#project-files-tab');
    }
}
