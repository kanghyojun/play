<?php
class Play extends Controller
{
  public function Play()
  {
    parent::Controller();
    $this->load->helper(array('html','form','url','asset'));
    $func = $this->uri->segment(2) ? $this->uri->segment(2) : 'index';

    $user_id = $this->get_user_id();

    if(empty($user_id) && $func != 'index') {
      redirect('/');
    }
    $this->load->helper('playmarkdown');

    $this->load->model('playmodel','play');
    $this->load->model('tagmodel','tag');
    $this->load->model('postmodel','post');
    $this->load->model('UserModel','user');
    $this->load->model('musicmodel','music');
  }
  public function index()
  {
    $user_id= $this->get_user_id();
    if(empty($user_id)) {
      $this->load->view('play/index',array("header" => "login required",
                                             "title" => "Play-Login Required"));
    } else {
      $user = $this->user->find($user_id); 
      $data = array("title"=>"hello","user"=>$user);
      redirect('/play/board');
    }
  }
  public function board($board_name = '')
  {

    if(!empty($board_name)) {
      $this->load->library('paging');
      $post_size = $this->play->get_post_size($board_name);

      $total_page = (int)(ceil($post_size/POST_PER_PAGE));

      $page = (int)($this->uri->segment(4));

      $selection = min(max(1,$page),$total_page);

      $offset = $selection * POST_PER_PAGE;
      $posts = $this->play->get_board_posts($board_name,POST_PER_PAGE,$offset);
      $data = array('board_title'=>$board_name,'title'=>'Board-view',
                    'posts' => $posts,
                    'board_name' => $board_name,
                    'total_page' => $total_page,
                    'selection' => $selection);
      $this->load->view('play/board',$data);
    } else {
      $data = array('new_boards'=>$this->play->get_new());
      $this->load->view('play/board_index',$data);
    }
  }
  public function search()
  {
    $search = $this->play->search_by_title($_POST['search']);
    $this->load->view('play/search',array('boards'=>$search));
  }
  public function random()
  {
    $this->load->view('play/view_posting',array('posts'=>$this->post->random(),
                                                'title'=>'random-play'));
  }
  public function music($alias)
  {
    if(empty($alias)) {
      echo 'redirection somewhere';
    } else {
      $query = $this->music->find_user_list_by_alias($alias);
      if(!$query) {
        echo 'you are the loser';
      } else {
        $this->load->helper('create_xml');
        $user = $this->user->find($this->get_user_id());
        create_xml($query,$user->alias);
        $this->load->view('play/music');
      }
    }
  }
  public function my($alias)
  {
    $this->load->view('play/view_posting',array('posts'=>$this->post->get_user_posts($alias)));
  }
  public function add_to_my()
  {
    $user_id = $this->get_user_id();

    if(!empty($_POST)) {
 
      if(!$this->music->create_list($user_id,$_POST['music_id'])) {
        redirect('/error/');
      } else {
        $user = $this->user->find($user_id);
        redirect('/play/music/'.$user->alias);
      }
    }
    redirect('/error/upload');
  }
  public function new_board()
  {
    $new = $this->play->get_new();
    $this->load->view('play/new',array('new_boards'=>$new)); 
  }
}
?>
