<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

/**
 * THE NORTHERN UGANDA STORY: GET /northern-uganda-story. The long, interactive version of the background that the About page summarises as a flow:
 * from Alice Lakwena and Joseph Kony to the displacement camps, the lost years of school and health, the 2006 ceasefire and why Lumina exists.
 * Content: app/config/story/northern-uganda.php (sourced; sources are listed on the page).
 */
final class StoryController extends Controller
{
    public function index(Request $request): Response
    {
        $story = (array) require APP_PATH . '/config/story/northern-uganda.php';
        return $this->render('web/story/index', ['story' => $story], [
            'bodyClass' => 'is-story',
            'meta' => [
                'title' => 'The Northern Uganda story: from the war to a digital future',
                'description' => 'How two decades of conflict, from Alice Lakwena to Joseph Kony, left a generation of Northern Uganda without school, health and skills, and how you can help young people catch up.',
            ],
            'breadcrumbs' => [['About', url('about')], ['The Northern Uganda story', null]],
        ]);
    }
}
