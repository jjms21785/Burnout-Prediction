<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    public function getQuestions()
    {
        $questionsFile = storage_path('app/questions.json');
        if (file_exists($questionsFile)) {
            $savedQuestions = json_decode(file_get_contents($questionsFile), true);
            if (is_array($savedQuestions)) {
                if (isset($savedQuestions[0]) && is_array($savedQuestions[0])) {
                    return $savedQuestions;
                }
                if (isset($savedQuestions['disengagement']) && isset($savedQuestions['exhaustion'])) {
                    return $this->migrateOldFormat($savedQuestions);
                }
            }
        }
        
        return $this->getDefaultQuestions();
    }
    
    private function migrateOldFormat($oldQuestions)
    {
        $allQuestions = array_merge(
            $oldQuestions['disengagement'] ?? [],
            $oldQuestions['exhaustion'] ?? []
        );
        
        usort($allQuestions, function($a, $b) {
            $aNum = intval(preg_replace('/[^0-9]/', '', $a['id']));
            $bNum = intval(preg_replace('/[^0-9]/', '', $b['id']));
            return $aNum - $bNum;
        });
        
        return $allQuestions;
    }
    
    public function getQuestionsForAssessment()
    {
        $questions = $this->getQuestions();
        
        usort($questions, function($a, $b) {
            $aNum = intval(preg_replace('/[^0-9]/', '', $a['id']));
            $bNum = intval(preg_replace('/[^0-9]/', '', $b['id']));
            return $aNum - $bNum;
        });
        
        $optionSets = $this->getOptionSets();
        
        $assessmentQuestions = [];
        foreach ($questions as $q) {
            $qNum = intval(preg_replace('/[^0-9]/', '', $q['id']));
            $assessmentQuestions[] = $this->formatQuestionForAssessment($q, $qNum, $optionSets);
        }
        
        return $assessmentQuestions;
    }
    
    private function formatQuestionForAssessment($question, $questionNumber, $optionSets)
    {
        $options = $this->getOptionsForQuestion($questionNumber, $question['type'] ?? 'neutral', $optionSets);
        
        return [
            'number' => $questionNumber,
            'text' => $question['text'],
            'name' => 'answers[' . ($questionNumber - 1) . ']',
            'options' => $options['options'],
            'gridCols' => $options['gridCols']
        ];
    }
    
    private function getOptionsForQuestion($questionNumber, $questionType, $optionSets)
    {
        if ($questionNumber <= 2) {
            if ($questionNumber == 1) {
                return ['options' => $optionSets['grade'], 'gridCols' => 'md:grid-cols-5'];
            } else {
                return ['options' => $optionSets['comparison'], 'gridCols' => 'md:grid-cols-5'];
            }
        }
        elseif ($questionNumber >= 3 && $questionNumber <= 6) {
            if ($questionNumber == 4 || $questionNumber == 5) {
                return ['options' => $optionSets['stressReversed'], 'gridCols' => 'md:grid-cols-5'];
            } else {
                return ['options' => $optionSets['stress'], 'gridCols' => 'md:grid-cols-5'];
            }
        }
        elseif ($questionNumber >= 7 && $questionNumber <= 8) {
            return ['options' => $optionSets['sleepTime'], 'gridCols' => 'md:grid-cols-5'];
        }
        elseif ($questionNumber == 9) {
            return ['options' => $optionSets['nights'], 'gridCols' => 'md:grid-cols-5'];
        }
        elseif ($questionNumber == 10) {
            return ['options' => $optionSets['quality'], 'gridCols' => 'md:grid-cols-5'];
        }
        elseif ($questionNumber >= 11 && $questionNumber <= 13) {
            return ['options' => $optionSets['extent'], 'gridCols' => 'md:grid-cols-5'];
        }
        elseif ($questionNumber == 14) {
            return ['options' => $optionSets['duration'], 'gridCols' => 'md:grid-cols-5'];
        }
        else {
            if ($questionType === 'positive') {
                return ['options' => $optionSets['olbiPositive'], 'gridCols' => 'md:grid-cols-4'];
            } else {
                return ['options' => $optionSets['olbiNegative'], 'gridCols' => 'md:grid-cols-4'];
            }
        }
    }
    
    public function getOptionSets()
    {
        return [
            'grade' => [
                ['value' => 4, 'label' => 'Excellent'],
                ['value' => 3, 'label' => 'Very Good'],
                ['value' => 2, 'label' => 'Good'],
                ['value' => 1, 'label' => 'Fair'],
                ['value' => 0, 'label' => 'Poor'],
            ],
            'comparison' => [
                ['value' => 4, 'label' => 'Much better'],
                ['value' => 3, 'label' => 'Somewhat better'],
                ['value' => 2, 'label' => 'About the same'],
                ['value' => 1, 'label' => 'Somewhat worse'],
                ['value' => 0, 'label' => 'Much worse'],
            ],
            'stress' => [
                ['value' => 4, 'label' => 'Very Often'],
                ['value' => 3, 'label' => 'Fairly Often'],
                ['value' => 2, 'label' => 'Sometimes'],
                ['value' => 1, 'label' => 'Almost Never'],
                ['value' => 0, 'label' => 'Never'],
            ],
            'stressReversed' => [
                ['value' => 0, 'label' => 'Very Often'],
                ['value' => 1, 'label' => 'Fairly Often'],
                ['value' => 2, 'label' => 'Sometimes'],
                ['value' => 3, 'label' => 'Almost Never'],
                ['value' => 4, 'label' => 'Never'],
            ],
            'sleepTime' => [
                ['value' => 4, 'label' => '0 to 15 mins'],
                ['value' => 3, 'label' => '16 to 30 mins'],
                ['value' => 2, 'label' => '31 to 45 mins'],
                ['value' => 1, 'label' => '46 to 60 mins'],
                ['value' => 0, 'label' => 'Greater than 60 mins'],
            ],
            'nights' => [
                ['value' => 4, 'label' => '0 to 1'],
                ['value' => 3, 'label' => '2'],
                ['value' => 2, 'label' => '3'],
                ['value' => 1, 'label' => '4'],
                ['value' => 0, 'label' => '5 to 7'],
            ],
            'quality' => [
                ['value' => 4, 'label' => 'Very good'],
                ['value' => 3, 'label' => 'Good'],
                ['value' => 2, 'label' => 'Average'],
                ['value' => 1, 'label' => 'Poor'],
                ['value' => 0, 'label' => 'Very Poor'],
            ],
            'extent' => [
                ['value' => 4, 'label' => 'Not at all'],
                ['value' => 3, 'label' => 'A little'],
                ['value' => 2, 'label' => 'Somewhat'],
                ['value' => 1, 'label' => 'Much'],
                ['value' => 0, 'label' => 'Very much'],
            ],
            'duration' => [
                ['value' => 4, 'label' => 'I don\'t have a problem / Less than 1 month'],
                ['value' => 3, 'label' => '1 - 2 months'],
                ['value' => 2, 'label' => '3 - 6 months'],
                ['value' => 1, 'label' => '7 - 12 months'],
                ['value' => 0, 'label' => 'More than 1 year'],
            ],
            'olbiPositive' => [
                ['value' => 1, 'label' => 'Strongly Agree'],
                ['value' => 2, 'label' => 'Agree'],
                ['value' => 3, 'label' => 'Disagree'],
                ['value' => 4, 'label' => 'Strongly Disagree'],
            ],
            'olbiNegative' => [
                ['value' => 4, 'label' => 'Strongly Agree'],
                ['value' => 3, 'label' => 'Agree'],
                ['value' => 2, 'label' => 'Disagree'],
                ['value' => 1, 'label' => 'Strongly Disagree'],
            ],
        ];
    }
    
    public function getDefaultQuestions()
    {
        return [
            ['id' => 'Q1', 'text' => 'How would you rate your grades last semester?', 'type' => 'neutral'],
            ['id' => 'Q2', 'text' => 'I am confident that compared to last semester, my grades this semester is', 'type' => 'neutral'],
            ['id' => 'Q3', 'text' => 'How often have you felt that you were unable to control the important things in your life?', 'type' => 'negative'],
            ['id' => 'Q4', 'text' => 'How often have you felt confident about your ability to handle your personal problems?', 'type' => 'positive'],
            ['id' => 'Q5', 'text' => 'How often have you felt that things were going your way?', 'type' => 'positive'],
            ['id' => 'Q6', 'text' => 'How often have you felt difficulties were piling up so high that you could not overcome them?', 'type' => 'negative'],
            ['id' => 'Q7', 'text' => 'How long does it take you to fall asleep?', 'type' => 'neutral'],
            ['id' => 'Q8', 'text' => 'If you then wake up during the night, how long are you awake for in total minutes?', 'type' => 'neutral'],
            ['id' => 'Q9', 'text' => 'How many nights a week do you have a problem with your sleep?', 'type' => 'negative'],
            ['id' => 'Q10', 'text' => 'How would you rate your sleep quality?', 'type' => 'neutral'],
            ['id' => 'Q11', 'text' => 'To what extent has poor sleep troubled you in general?', 'type' => 'negative'],
            ['id' => 'Q12', 'text' => 'To what extent has poor sleep affected your mood, energy, or relationships?', 'type' => 'negative'],
            ['id' => 'Q13', 'text' => 'To what extent has poor sleep affected your concentration, productivity, or ability to stay awake?', 'type' => 'negative'],
            ['id' => 'Q14', 'text' => 'How long have you had a problem with your sleep?', 'type' => 'negative'],
            ['id' => 'Q15', 'text' => 'I always find new and interesting aspects in my studies.', 'type' => 'positive'],
            ['id' => 'Q16', 'text' => 'There are days when I feel tired before I arrive in class or before I start studying.', 'type' => 'negative'],
            ['id' => 'Q17', 'text' => 'I can usually manage my study-related workload well.', 'type' => 'positive'],
            ['id' => 'Q18', 'text' => 'Over time, one can become disconnected from this type of study.', 'type' => 'negative'],
            ['id' => 'Q19', 'text' => 'I find my studies to be challenging but helpful.', 'type' => 'positive'],
            ['id' => 'Q20', 'text' => 'After a class or after studying, I tend to need more time now, than in the past in order to relax and feel better.', 'type' => 'negative'],
            ['id' => 'Q21', 'text' => 'I can tolerate the pressure of my studies very well.', 'type' => 'positive'],
            ['id' => 'Q22', 'text' => 'Lately, I tend to think less about my academic tasks and do them almost automatically.', 'type' => 'negative'],
            ['id' => 'Q23', 'text' => 'After a class or after studying, I have enough energy for my leisure activities.', 'type' => 'positive'],
            ['id' => 'Q24', 'text' => 'I feel more and more engaged in my studies.', 'type' => 'positive'],
            ['id' => 'Q25', 'text' => 'While studying, I usually feel emotionally drained.', 'type' => 'negative'],
            ['id' => 'Q26', 'text' => 'It happens more and more often that I talk about my studies in a negative way.', 'type' => 'negative'],
            ['id' => 'Q27', 'text' => 'This is the only field of study that I can imagine myself doing.', 'type' => 'positive'],
            ['id' => 'Q28', 'text' => 'After a class or after studying, I usually feel worn out and weary.', 'type' => 'negative'],
            ['id' => 'Q29', 'text' => 'When I study, I usually feel energized.', 'type' => 'positive'],
            ['id' => 'Q30', 'text' => 'Sometimes I feel sickened by my studies.', 'type' => 'negative'],
        ];
    }
    
    public function updateQuestions(Request $request)
    {
        try {
            $questionsInput = $request->input('questions');
            
            if (empty($questionsInput) || !is_array($questionsInput)) {
                return back()->with('error', 'No questions data received. Please try again.');
            }
            
            $savedQuestions = [];
            
            foreach ($questionsInput as $question) {
                if (!isset($question['id']) || !isset($question['text'])) {
                    continue;
                }
                
                $savedQuestions[] = [
                    'id' => $question['id'],
                    'text' => $question['text'],
                    'type' => $question['type'] ?? 'neutral'
                ];
            }
            
            usort($savedQuestions, function($a, $b) {
                $aNum = intval(preg_replace('/[^0-9]/', '', $a['id']));
                $bNum = intval(preg_replace('/[^0-9]/', '', $b['id']));
                return $aNum - $bNum;
            });
            
            $questionsFile = storage_path('app/questions.json');
            $questionsDir = dirname($questionsFile);
            
            if (!is_dir($questionsDir)) {
                mkdir($questionsDir, 0755, true);
            }
            
            $jsonContent = json_encode($savedQuestions, JSON_PRETTY_PRINT);
            if ($jsonContent === false) {
                throw new \Exception('Failed to encode questions as JSON');
            }
            
            $bytesWritten = file_put_contents($questionsFile, $jsonContent);
            if ($bytesWritten === false) {
                throw new \Exception('Failed to write questions file. Check storage/app directory permissions.');
            }
            
            if (!file_exists($questionsFile)) {
                throw new \Exception('Questions file was not created.');
            }
        
            return back()->with('success', 'Questions updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update questions: ' . $e->getMessage());
            return back()->with('error', 'Failed to update questions: ' . $e->getMessage());
        }
    }
}
