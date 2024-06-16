jQuery(document).ready(function ($) {
    // Gantt Chart Initialization
    if (typeof ganttTasksData !== 'undefined' && ganttTasksData.tasks.length > 0) {
        var tasks = ganttTasksData.tasks.map(function(task) {
            var startDate = task.start_date ? new Date(task.start_date) : null;
            var endDate = task.end_date ? new Date(task.end_date) : null;

            if (startDate && endDate) {
                return {
                    id: task.id,
                    name: task.task_name + ' (' + task.user_name + ')',
                    start: startDate,
                    end: endDate,
                    progress: task.is_completed ? 100 : 0,
                    custom_class: 'bar-' + task.user_id
                };
            }
            return null;
        }).filter(function(task) {
            return task !== null;
        });

        if (tasks.length > 0) {
            var gantt = new Gantt("#gantt-chart", tasks, {
                on_click: function (task) {
                    console.log(task);
                },
                on_date_change: function (task, start, end) {
                    console.log(task, start, end);
                },
                on_progress_change: function (task, progress) {
                    console.log(task, progress);
                },
                on_view_change: function (mode) {
                    console.log(mode);
                }
            });
        } else {
            $('#gantt-chart').html('<p>No hay tareas con fechas válidas para mostrar en el diagrama de Gantt.</p>');
        }
    } else {
        $('#gantt-chart').html('<p>No hay tareas para mostrar en el diagrama de Gantt.</p>');
    }
});
