import React, { useState } from 'react';
import axios from 'axios';

const TaskList = ({ day, tasks, refreshTasks }) => {
  const [isAdding, setIsAdding] = useState(false);
  const [taskInput, setTaskInput] = useState('');

  // Get user ID from localStorage
  const userId = localStorage.getItem('hidden-id-user');

  // Handle task completion by toggling the completed state
  const handleTaskCompletion = async (taskId) => {
    try {
      await axios.post(
        'http://localhost:8000/backend/calendar/tasks.php',
        new URLSearchParams({
          taskId,
          userId,
        }),
      );
      refreshTasks(day);
    } catch (error) {
      console.error('Error marking task as completed:', error);
    }
  };

  const handleAddTaskClick = () => {
    setIsAdding(!isAdding);
  };

  const handleTaskInputChange = (event) => {
    setTaskInput(event.target.value);
  };

  const handleTaskSubmit = async (event) => {
    event.preventDefault();
    try {
      const formattedDate = startOfDayUTC(day).toISOString().split('T')[0];
      await axios.post(
        'http://localhost:8000/backend/calendar/tasks.php',
        new URLSearchParams({
          date: formattedDate,
          task: taskInput,
          userId, // Add userId here
        }),
      );
      setTaskInput('');
      setIsAdding(false);
      refreshTasks(day);
    } catch (error) {
      console.error('Error adding task:', error);
    }
  };

  const formattedDate = day.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });

  return (
    <div className="bg-white rounded-xl p-4 shadow-md w-[90%] relative flex flex-col items-start justify-center gap-3">
      <div className="flex flex-col items-center justify-center gap-2 bg-blue-50 rounded-xl p-4 w-full">
        <h3 className="text-center">
          Tareas para <br />
          {formattedDate}
        </h3>
      </div>
      <button className="absolute top-3 right-3 bg-blue-500 text-white rounded-full font-sans font-bold text-2xl text-center flex flex-row justify-center items-center h-10 w-10 pb-0.5" onClick={handleAddTaskClick}>
        <p>+</p>
      </button>

      {Array.isArray(tasks) ? (
        tasks.length > 0 ? (
          <ul className="flex flex-col gap-2 ml-4">
            {tasks.map((task) => (
              <li key={task.id} className="flex items-center gap-2">
                <input type="checkbox" checked={task.completed} onChange={() => handleTaskCompletion(task.id)} className="form-checkbox h-5 w-5 text-green-600" />
                <span className={task.completed ? 'line-through text-gray-500' : ''}>{task.task}</span>
              </li>
            ))}
          </ul>
        ) : (
          <p className="text-gray-500 italic">No hay tareas pendientes</p>
        )
      ) : (
        <p className="text-red-500">Tasks data is not an array</p>
      )}
      {isAdding && (
        <form onSubmit={handleTaskSubmit} className="transition-transform transform rounded-md mt-2 flex flex-row items-center justify-center gap-2 w-full">
          <input type="text" value={taskInput} onChange={handleTaskInputChange} placeholder="Añade una tarea" className="border rounded-md p-2 w-full" required />
          <button type="submit" className="bg-green-500 text-white rounded-md py-2 px-3">
            +
          </button>
        </form>
      )}
    </div>
  );
};

const startOfDayUTC = (date) => {
  const utcDate = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
  return utcDate;
};

export default TaskList;
