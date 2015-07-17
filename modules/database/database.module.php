<?php
	class database_module {

		public function __construct()
		{
			$this->opus =& opus::$instance;

			$this->db = new mysqli( $this->opus->config->database->host,
									$this->opus->config->database->username,
									$this->opus->config->database->password,
									$this->opus->config->database->database
								);

			if ($this->db->connect_errno > 0)
			    exit('Unable to connect to database [' . $this->db->connect_error . ']');
		}

		public function get_result($get_settings)
		{
			$where = (isset($get_settings['where']) && $get_settings['where'] !== FALSE) ? $get_settings['where'] : FALSE;
			$where_like = (isset($get_settings['where_like']) && $get_settings['where_like'] !== FALSE) ? $get_settings['where_like'] : FALSE;
			$order_by = (isset($get_settings['order_by']) && $get_settings['order_by'] !== FALSE) ? $get_settings['order_by'] : FALSE;
			$limit = (isset($get_settings['limit_offset']) && isset($get_settings['limit_count'])) ? $get_settings['limit_offset'] . ', ' . $get_settings['limit_count'] : FALSE;

			$sql = 'SELECT ';
			$x = 0;

			foreach ($get_settings['select'] as $column)
			{
				$sql .= $column;

				if ($x < count($get_settings['select']) - 1)
					$sql .= ', ';

				$x++;
			}

			$sql .= ' FROM ' . $get_settings['data_model']['db_table'];

			if ($where !== FALSE)
			{
				$sql .= ' WHERE ' . key($where) . ' = ';
				$sql .= is_numeric(current($where)) ? current($where) : '"' . current($where) . '"';
			}

			if ($where_like !== FALSE)
			{
				$sql .= ' WHERE ' . key($where_like) . ' LIKE ';
				$sql .= '"%' . current($where_like) . '%"';
			}

			if ($order_by !== FALSE)
				$sql .= ' ORDER BY ' . $order_by;

			if ($limit !== FALSE)
				$sql .= ' LIMIT ' . $limit;

			$this->opus->log->write('debug', 'SQL Query: ' . $sql);
			$result = $this->db->query($sql);

			if (! $result)
			    die('Database error: '. $this->db->error);
			else
			{
				if (isset($get_settings['get_total_rows']) && $get_settings['get_total_rows'] === TRUE)
				{
					//Also get number of rows for the table, for pagination mostly. Fastest way seems to be a separate query.
					$total_rows_sql = 'SELECT COUNT(*) AS total_rows FROM ' . $get_settings['data_model']['db_table'];

					if ($where !== FALSE)
					{
						$total_rows_sql .= ' WHERE ' . key($where) . ' = ';
						$total_rows_sql .= is_numeric(current($where)) ? current($where) : '"' . current($where) . '"';
					}

					if ($where_like !== FALSE)
					{
						$total_rows_sql .= ' WHERE ' . key($where_like) . ' LIKE ';
						$total_rows_sql .= '"%' . current($where_like) . '%"';
					}

					$total_rows_result = $this->db->query($total_rows_sql);
					$total_rows_result = mysqli_fetch_object($total_rows_result);

					$result->total_rows = $total_rows_result->total_rows;
				}

				return $result;
			}
		}

		public function get_row($get_settings)
		{
			$sql = 'SELECT ';
			$x = 0;

			foreach ($get_settings['select'] as $column)
			{
				$sql .= $column;

				if ($x < count($get_settings['select']) - 1)
					$sql .= ', ';

				$x++;
			}

			$sql .= ' FROM ' . $get_settings['data_model']['db_table'];
			$sql .= ' WHERE ' . key($get_settings['where']) . ' = ';
			$sql .= is_numeric(current($get_settings['where'])) ? current($get_settings['where']) : '"' . current($get_settings['where']) . '"';
			$sql .= " LIMIT 1";

			$this->opus->log->write('debug', 'SQL Query: ' . $sql);
			$result = $this->db->query($sql);

			if (! $result)
			    die('Database error: '. $this->db->error);

			return $result->fetch_assoc();
		}

		public function insert($insert_settings)
		{
			$this->db->form_errors = $this->opus->form->validate($insert_settings['data_model']);

			if (! isset($this->db->form_errors))
			{
				$keys = "";
				$changes = "";
				$values = array();
				$x = 1;
				$value_types = "";

				foreach ($insert_settings['data_model']['fields'] as $column_name => $column_settings)
				{
					if (in_array($column_settings['form_name'], $insert_settings['fields']) && isset($_POST[$column_settings['form_name']]))
					{
						$keys .= $column_name;
						$changes .= '?';
						$values[] = $_POST[$column_name];
						$value_types .= is_numeric($_POST[$column_name]) ? "i" : "s";

						if ($x < count($insert_settings['fields']))
						{
							$keys .= ", ";
							$changes .= ", ";
						}

						$x++;
					}


				}

				if (isset($insert_settings['data_model']['db_table']) && ! empty($keys) && ! empty($values))
				{
					$sql = 'INSERT INTO ' . $insert_settings['data_model']['db_table'] . ' (' . $keys . ') VALUES (' . $changes . ');';
					$this->opus->log->write('debug', 'SQL Query: ' . $sql);

					$this->make_statement($sql, $values, $value_types);
				}
			}

			return $this->db;
		}

		public function update($update_settings)
		{
			//Validate the input and add errors to list
			$this->db->form_errors = $this->opus->form->validate($update_settings['data_model']);

			if (! isset($this->db->form_errors))
			{
				$changes = "";
				$value_types = "";
				$values = array();
				$x = 0;

				foreach ($update_settings['fields'] as $column)
				{
					if (isset($_POST[$column]))
					{
						$changes .= $column . ' = ?';
						$values[] = $_POST[$column];
						$value_types .= (is_numeric($_POST[$column])) ? "i" : "s";

						if ($x < count($update_settings['fields']) - 1)
							$changes .= ", ";
					}

					$x++;
				}

				if (isset($update_settings['data_model']['db_table']) && ! empty($changes))
				{
					$sql = 'UPDATE ' . $update_settings['data_model']['db_table'] . ' SET ' . $changes;
					$sql .= ' WHERE ' . key($update_settings['where']) . ' = ?';
					$value_types .= is_numeric(current($update_settings['where'])) ? "i" : "s";

					$this->opus->log->write('debug', 'SQL Query: ' . $sql);

					$values[] = array_values($update_settings['where'])[0];

					$this->make_statement($sql, $values, $value_types);
				}
			}

			return $this->db;
		}

		public function delete($delete_settings)
		{
			$sql = "DELETE FROM " . $delete_settings['data_model']['db_table'] . " WHERE " . key($delete_settings['where']) . " = ?";
			$values = array(current($delete_settings['where']));
			$value_types = is_numeric($delete_settings['where']) ? 'i' : 's';

			$this->make_statement($sql, $values, $value_types);

			$this->opus->log->write('debug', 'SQL Query: ' . $sql);

			return $this->db;
		}

		private function make_statement($sql, $values, $value_types) {
			/* Create a bind_param with array values instead of strings */

			//Prepare the statement
			if (! $statement = $this->db->prepare($sql))
				exit($this->db->error);

			//Add the value types to the array, which could look like "ssiis"
			array_unshift($values, $value_types);

			//To use call_user_func_array, we need to pass the array by reference
			$reference_array = array();

			foreach($values as $key => $value)
				$reference_array[$key] = &$values[$key];

			//Make the bind_param
			if (! call_user_func_array(array($statement, 'bind_param'), $reference_array))
				exit($statement->error);

			//Execute the query
			if (! $statement->execute())
				exit($statement->error);

			//We are done here...
			$statement->close();
		}
		
	}

?>