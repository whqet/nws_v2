<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * 活动列表
 * 
 * 
 *
 * @copyright  版权所有(C) 2014-2014 沈阳工业大学ACM实验室 沈阳工业大学网络管理中心 *Chen
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt   GPL3.0 License
 * @version    2.0
 * @link       http://acm.sut.edu.cn/
 * @since      File available since Release 2.0
*/
class Act_list extends CI_Controller{
    function __construct() {
        parent::__construct();
    }
    
    public function index(){
        $this->load->library('session');
        $this->load->library('encrypt');
        $this->load->library('authorizee');
        $this->load->model('section_model');
        $this->load->model('act_model');
        if (!$this->session->userdata('user_id')){
            header('Location: ' . base_url());
            return 0;
        }
        //权限获取
        
        $this->load->view('act_list_view', array(
            'user_id' => $this->session->userdata('user_id'),
            'user_key' => $this->encrypt->encode($this->session->userdata('user_key')),
            'authorizee_act_update' => $this->authorizee->CheckAuthorizee('act_update', $this->session->userdata('user_id')),
            'authorizee_act_dele' => $this->authorizee->CheckAuthorizee('act_dele', $this->session->userdata('user_id')),
            'authorizee_act_global_list' => $this->authorizee->CheckAuthorizee('act_global_list', $this->session->userdata('user_id')),
            'authorizee_act_propagator' => $this->authorizee->CheckAuthorizee('act_propagator', $this->session->userdata('user_id')),
            'act_section' => $this->section_model->GetSectionNameList(),
            'act_type' => $this->act_model->GetActTypeList()
        ));
    }
    
    /**    
     *  @Purpose:    
     *  移动端活动列表查询
     *  
     *  @Method Name:
     *  MobileGetActList()    
     *  @Parameter: 
     *  POST array(
     *      'user_key' 用户识别码
     *      array data_data(
     *          array(
     *              
     *          )
     *      )
     *  )   
     *  @Return: 
     *  状态码|状态
     *      0|密钥无法通过安检
     *      1|添加成功
     *      2|活动名称不可为空或超过198个字符
     *      3|活动类型不存在或超过48个字符
     *      4|部门名称不存在或超过28个字符
     *      5|活动描述不可为空或超过998个字符
     *      6|活动注意事项超过998个字符
     *      7|活动开始时间不合法，请尝试把输入法关闭
     *      8|活动结束时间不合法，请尝试把输入法关闭
     *      9|需要资金格式为小于10位的整数
     *      10|活动地点不能超过198个字符
     *      11|加入人数限制必须为小于10位的数字
     *      12|添加失败
     *      13|用户无权限
     *      14|用户无添加其他部门活动的权限
     *      15|内部活动传值错误
     *      
     * 
    */
    public function MobileGetActList(){
        
    }
    
    /**    
     *  @Purpose:    
     *  初始化活动列表查询
     *  
     *  @Method Name:
     *  GetActGlobeListInit()    
     *  @Parameter: 
     *  POST array(
     *      'user_key' 用户识别码
     *      'user_id'  用户id
     *      )
     *  )   
     *  @Return: 
     *  状态码|状态
     *      0|密钥无法通过安检
     *      1|array $data = array ();
     *      2|用户无权限
     * 
    */
    public function GetActGlobeListInit(){
        $this->load->library('secure');  
        $this->load->library('data');
        $this->load->library('authorizee');
        $this->load->model('act_model');
        if ($this->input->post('user_id', TRUE) != $this->secure->CheckUserKey($this->input->post('user_key', TRUE))){
            $this->data->Out('iframe', $this->input->post('src', TRUE), 0, '密钥无法通过安检');
        }
        
        if (!$this->authorizee->CheckAuthorizee('act_global_list', $this->input->post('user_id', TRUE))){
            $this->data->Out('iframe', $this->input->post('src', TRUE), 2, '用户无权限');
        }
        
        //初始化列表，从1开始拉取到10.无需再次设计函数        
        $data = $this->act_model->GetActList(0, 10);
        $this->data->Out('iframe', $this->input->post('src', TRUE), 1, 'GetActGlobeInit', $data);
    }
    
    
    /**    
     *  @Purpose:    
     *  获取活动详细信息
     *  
     *  @Method Name:
     *  GetActInfo()    
     *  @Parameter: 
     *  POST array(
     *      'user_key'  用户识别码
     *      'user_id'   用户id
     *      'act_id'    活动id
     *      )
     *  )   
     *  @Return: 
     *  状态码|状态
     *      -1|密钥无法通过安检
     *      -2|活动id传值错误
     *      -3|活动不存在
     *      -4|用户没有查看已删除活动的权限
     *      
     * 
    */
    public function GetActInfo(){
        $this->load->library('secure');  
        $this->load->library('data');
        $this->load->library('authorizee');
        $this->load->model('act_model');
        if ($this->input->post('user_id', TRUE) != $this->secure->CheckUserKey($this->input->post('user_key', TRUE))){
            $this->data->Out('iframe', $this->input->post('src', TRUE), -1, '密钥无法通过安检');
        }
        
        //从这里开始，错误代码改为-数
        if (!ctype_digit($this->input->post('act_id', TRUE))){
            $this->data->Out('iframe', $this->input->post('src', TRUE), -2, '活动id传值错误');
        }
        
        //验证活动是否存在
        if (!$this->act_model->CheckIdExist($this->input->post('act_id', TRUE))){
            $this->data->Out('iframe', $this->input->post('src', TRUE), -3, '活动不存在');
        }    
        
        $data = $this->act_model->GetActInfo($this->input->post('act_id', TRUE));
        
        if ($data[0]['act_defunct'] && !$this->authorizee->CheckAuthorizee('act_read_dele', $this->input->post('user_id', TRUE))){
            $this->data->Out('iframe', $this->input->post('src', TRUE), -4, '用户没有查看已删除活动的权限');
        }
        
        $this->data->Out('iframe', $this->input->post('src', TRUE), 1, 'GetActInfo', $data[0]);
    }
        
    /**    
     *  @Purpose:    
     *  重绘活动列表
     *  
     *  @Method Name:
     *  RedrawActList()    
     *  @Parameter: 
     *  POST array(
     *      'user_key'  用户识别码
     *      'user_id'   用户id
     *      'act_list'  当前活动列表
     *      )
     *  @Return: 
     *  状态码|状态
     *     
     *      
     *      
     * 
    */
    public function RedrawActList(){
        $this->load->library('secure');  
        $this->load->library('data');
        $this->load->library('authorizee');
        $this->load->model('act_model');
        if ($this->input->post('user_id', TRUE) != $this->secure->CheckUserKey($this->input->post('user_key', TRUE))){
            $this->data->Out('iframe', $this->input->post('src', TRUE), -1, '密钥无法通过安检');
        }
        
        //从这里开始，错误代码改为-数
        if (!ctype_digit($this->input->post('act_id', TRUE))){
            $this->data->Out('iframe', $this->input->post('src', TRUE), -2, '活动id传值错误');
        }
        
        //验证活动是否存在
        if (!$this->act_model->CheckIdExist($this->input->post('act_id', TRUE))){
            $this->data->Out('iframe', $this->input->post('src', TRUE), -3, '活动不存在');
        }    
        
        $data = $this->act_model->GetActInfo($this->input->post('act_id', TRUE));
        
        if ($data[0]['act_defunct'] && !$this->authorizee->CheckAuthorizee('act_read_dele', $this->input->post('user_id', TRUE))){
            $this->data->Out('iframe', $this->input->post('src', TRUE), -4, '用户没有查看已删除活动的权限');
        }
        
        $this->data->Out('iframe', $this->input->post('src', TRUE), 1, 'GetActInfo', $data[0]);
    }
    
    
}