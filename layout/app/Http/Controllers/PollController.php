<?php

namespace App\Http\Controllers;

use App\Models\article;
use Illuminate\Http\Request;
use Layout\Website\Services\ThemeService;
use App\Http\Controllers\CommonController;
use App\Models\site_poll;
use App\Models\site_poll_vote;
use App\Models\site_poll_answer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class PollController extends Controller {

    public function __construct() {
        
    }

    public static function poll($articleId) {

        if ($articleId) {
            $pollInfoArray = site_poll::find_article($articleId)->orderBy('np_poll_id','desc')->get();

            if(!isset($pollInfoArray[0])){
                return '';

            }else{
                $pollId = $pollInfoArray[0]->np_poll_id;

                if ($pollId) {
                    $pollArray = site_poll::find_np($pollId);
                    $answersInfo = site_poll_answer::find_poll($pollInfoArray[0]->site_poll_id);
                    $userIp = \Request::getClientIp(true);
                } else {
                    return '';
                }

            }
        } else {
            return '';
        }

        $poll_results = self::GetPollResults($pollArray->site_poll_id);
        $total_votes = $poll_results['total_count'];

        $Allpolls = array('pollArray' => $pollArray, 'answersInfo' => $answersInfo, 'poll_results' => $poll_results, 'total_votes' => $total_votes);

        return $Allpolls;
    }
    
    public static function voteAction(Request $request) {

        $pollId = $request->poll_id;
        $answerId = $request->answer_id;

        $userIp = AjaxController::getIP();
        $answerId = is_array($answerId) ? $answerId : array($answerId);

        foreach ($answerId as $key => $id) {
            $id = !is_numeric($id) ? ( $id == 'on' ? $id : 0 ) : $id;
            if ($pollId) {
                site_poll_vote::insert([
                    'site_poll_vote_ip' => $userIp,
                    'site_poll_id' => $pollId,
                    'site_poll_answer_id' => $id
                ]);
            }
        }

        $response = array();
        $response['message'] = '1';

        if (!isset($_COOKIE['poll_cookie'])) {
            setcookie('poll_cookie', '');
            $_COOKIE['poll_cookie'] = '';
        }

        $cookie = $_COOKIE['poll_cookie'];

        $cookie = explode(',', $cookie); {
            if (empty($cookie[0])) {
                $cookie[0] = $pollId;
            } else if (!in_array($pollId, $cookie)) {
                $cookie[] = $pollId;
            }
        }
        $cookie = implode(',', $cookie);



        if ($pollId) {
            setcookie('poll_cookie', $cookie, strtotime('+30 days'));
        }


        return $response;
    }

    public function getPollResultsHTMLAction(Request $request) {
        $pollId = $request->poll_id;
        $pollDetails = self::GetPollDetails($pollId);
        $html = view('theme::poll.poll_results', ['poll' => $pollDetails])->render();

        $data_array = array(
            "html" => $html,
            "poll_site_id" => $pollId
        );

        return $data_array;
    }

    public static function GetPollDetails($pollId) {

        if ($pollId) {
            $pollArray = site_poll::where("site_poll_id", '=', $pollId)->get();
            $answersInfo = site_poll_answer::find_poll($pollArray[0]->site_poll_id);
            $userIp = \Request::getClientIp(true);
            $poll_results = self::GetPollResults($pollArray[0]->site_poll_id);
            $total_votes = $poll_results['total_count'];
            $Allpolls = array('pollArray' => $pollArray, 'answersInfo' => $answersInfo, 'poll_results' => $poll_results, 'total_votes' => $total_votes);
        } else {
            $message = 'No polls found';
        }

        return $Allpolls;
    }

    public static function GetPollResults($poll_id) {
        $pollInfo = site_poll_vote::select('site_poll_answer_id', DB::raw('COUNT(site_poll_answer_id) as count'))
                ->where("site_poll_id", '=', $poll_id)
                ->groupBy('site_poll_answer_id')
                ->get();

        $pollResults['total_count'] = 0;

        foreach ($pollInfo as $voteCountInfo) {
            $pollResults[$voteCountInfo->site_poll_answer_id] = $voteCountInfo->count;
            $pollResults['total_count'] += $voteCountInfo->count;
        }

        return $pollResults;
    }
    
    public static function allPollForArticle($articleId) {
        $allPollInfoArray=array();
        if ($articleId) {
            
            $pollsInfoArray = site_poll::find_article($articleId)->orderBy('site_poll_id','asc')->get();

            foreach ($pollsInfoArray as $poll){
                $pollId=$poll->np_poll_id;
                if($pollId){
                    $pollArray[$poll->np_poll_id] = site_poll::find_np($pollId);
                    $answersInfo[$poll->np_poll_id] = site_poll_answer::find_poll($poll->site_poll_id); 
                    $correctAnswer[$poll->np_poll_id] = self::getQuizCorrectAnswer($poll->site_poll_id); 
                    $poll_results[$poll->np_poll_id] = self::GetPollResults($poll->site_poll_id);
                    $total_votes[$poll->np_poll_id] = $poll_results[$poll->np_poll_id]['total_count'];
                    $allPollInfoArray[] = array('sitePollId' => $poll->site_poll_id,'pollArray' => $pollArray[$poll->np_poll_id], 'answersInfo' => $answersInfo[$poll->np_poll_id],'correctAnswer'=>$correctAnswer[$poll->np_poll_id],'poll_results' => $poll_results[$poll->np_poll_id], 'total_votes' => $total_votes[$poll->np_poll_id]);
                }
            } 
        } 
        
        return $allPollInfoArray;
    }

    
    public static function quizChooseAnswerAction(Request $request) {

        $pollId = $request->poll_id;
        $answerId = $request->answer_id;
        $articleId = $request->article_id;
        $response = array(); 

        $userIp = AjaxController::getIP(); 
        
        $correctAnswer=self::getQuizCorrectAnswer($pollId);
        
        $currentPoll=site_poll::where('site_poll_id',$pollId)->first();   
        $allQuestion=site_poll::where('np_article_id',$currentPoll->np_article_id)->get(); 
        $lastPoll=site_poll::where('np_article_id',$currentPoll->np_article_id)->orderBy('site_poll_id','desc')->first(); 
        $yourChoiceTile=self::getAnswerTitle($answerId);
        $allpolls = self::allPollForArticle($currentPoll->np_article_id);
        $htmlresults='';
        
        if(isset($_COOKIE['quiz_cookie'])) { 
        $cookieData = json_decode($_COOKIE['quiz_cookie'], true); 
        $response=$cookieData;
        }

         if(!isset($_COOKIE['quiz_cookie']) || ( isset($_COOKIE['quiz_cookie']) && !isset($cookieData[$pollId]) ) ) {  
               
            if ($pollId) {
                site_poll_vote::insert([
                    'site_poll_vote_ip' => $userIp,
                    'site_poll_id' => $pollId,
                    'site_poll_answer_id' => $answerId
                ]);
            }  
            $response[$pollId]['yourChoice'] = $answerId;
            $response[$pollId]['yourChoiceTile'] = $yourChoiceTile->site_poll_answer_title;
            $response[$pollId]['correctAnswer'] = $correctAnswer->site_poll_answer_id;  
            $correctAnswers=0;
            if($pollId==$lastPoll->site_poll_id){
                $response['article-'.$articleId]['finishQuiz']=true;
                foreach($allQuestion as $ques){ 
                    if($response[$ques->site_poll_id]['yourChoice']==$response[$ques->site_poll_id]['correctAnswer']){
                      $correctAnswers++;  
                    }
                }
                $response['article-'.$articleId]['correctAnswers']=$correctAnswers;
                $response['article-'.$articleId]['countQuestion']=count($allQuestion);
                $htmlresults =  view('theme::ajax.get_full_results', ['quiz_cookie'=>$response,'polls'=>$allpolls])->render(); 
            } 
        
            setcookie('quiz_cookie', json_encode($response), strtotime('+1 days'),'/');
            
        }else{ 
            $response = json_decode($_COOKIE['quiz_cookie'], true); 
        }   
        
        $data_array = array(
            "htmlresults" => $htmlresults,
            "response" => $response 
        ); 
        return $data_array;  
 
    }
    
    public static function getQuizCorrectAnswer($pollId) {  
        $correctAnswer = site_poll_answer::where("site_poll_id", $pollId)->where('site_poll_correct_answer','1')->first();  
        return $correctAnswer;
    }
    public static function getAnswerTitle($answerId) {  
        $answerTitle = site_poll_answer::where("site_poll_answer_id", $answerId)->first();  
        return $answerTitle;
    }
    
    public static function getNextPollQuiz(Request $request){  
        $currentPollId = $request->currentPollId; 
        $view = $request->view;   
        $nextPoll=site_poll::where('site_poll_id','>',$currentPollId)->orderBy('site_poll_id','asc')->first();   
        $lastPoll=site_poll::where('np_article_id',$nextPoll->np_article_id)->orderBy('site_poll_id','desc')->first();
        $answersInfo = site_poll_answer::find_poll($nextPoll->site_poll_id);
        $correctAnswer = self::getQuizCorrectAnswer($nextPoll->site_poll_id); 
        $quiz_cookie=json_decode($_COOKIE['quiz_cookie'], true);
        if(!isset($quiz_cookie[$nextPoll->site_poll_id])){
            $disableNext='disable';
            $active='active';
        }else{
            $disableNext='';
            $active='';
        }
        if($nextPoll->site_poll_id==$lastPoll->site_poll_id){
            $hideNext=true;
        }else{
            $hideNext=false;
        }
        $html =  view('theme::ajax.'.$view, ['poll'=>$nextPoll, 'answersInfo' => $answersInfo,'quiz_cookie'=>$quiz_cookie,'correctAnswer'=>$correctAnswer])->render(); 
        
        $data_array = array(
            "html" => $html, 
            "pollId"=>$nextPoll->site_poll_id,
            "disableNext"=>$disableNext,
            "activeCircle"=>$active,
            "hideNext"=>$hideNext
        ); 
        return $data_array;  
    }
    
    public static function getPreviousPollQuiz(Request $request){  
        $currentPollId = $request->currentPollId; 
        $view = $request->view;   
        $nextPoll=site_poll::where('site_poll_id','<',$currentPollId)->orderBy('site_poll_id','desc')->first();  
        $firstPoll=site_poll::where('np_article_id',$nextPoll->np_article_id)->orderBy('site_poll_id','asc')->first();
        $answersInfo = site_poll_answer::find_poll($nextPoll->site_poll_id);
        $correctAnswer = self::getQuizCorrectAnswer($nextPoll->site_poll_id); 
        $quiz_cookie=json_decode($_COOKIE['quiz_cookie'], true);  
        if($nextPoll->site_poll_id==$firstPoll->site_poll_id){
            $hideFirst=true;
        }else{
            $hideFirst=false;
        }
        $html =  view('theme::ajax.'.$view, ['poll'=>$nextPoll, 'answersInfo' => $answersInfo,'quiz_cookie'=>$quiz_cookie,'correctAnswer'=>$correctAnswer])->render(); 
        $data_array = array(
            "html" => $html,
            "pollId"=>$nextPoll->site_poll_id,
            "hideFirst"=>$hideFirst
        ); 
        return $data_array;  
    }

    
}
