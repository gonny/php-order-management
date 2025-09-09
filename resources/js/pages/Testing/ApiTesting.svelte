<script lang="ts">
  import { type PageProps } from '@inertiajs/core';
  import { router } from '@inertiajs/svelte';
  import { AppLayout } from '~/layouts';
  import { Button } from '~/components/ui/button';
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '~/components/ui/card';
  import { Label } from '~/components/ui/label';
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '~/components/ui/select';
  import { Textarea } from '~/components/ui/textarea';
  import { Input } from '~/components/ui/input';
  import { Badge } from '~/components/ui/badge';
  import { Tabs, TabsContent, TabsList, TabsTrigger } from '~/components/ui/tabs';
  import { Play, Copy, Download, History, ArrowLeft } from 'lucide-svelte';

  interface PayloadTemplate {
    name: string;
    description: string;
    template: Record<string, any>;
  }

  interface ApiEndpoint {
    method: string;
    path: string;
    description: string;
  }

  interface EndpointGroup {
    group: string;
    endpoints: ApiEndpoint[];
  }

  interface ApiClient {
    id: number;
    name: string;
    key_id: string;
    is_active: boolean;
  }

  interface Props extends PageProps {
    payloadTemplates: Record<string, PayloadTemplate>;
    endpoints: EndpointGroup[];
    apiClients: ApiClient[];
  }

  let { payloadTemplates, endpoints, apiClients }: Props = $props();

  let selectedMethod = $state('GET');
  let selectedEndpoint = $state('/api/v1/health');
  let selectedApiClient = $state(apiClients[0]?.id || null);
  let payloadText = $state('{}');
  let selectedTemplate = $state('');
  let testResponse = $state<any>(null);
  let isLoading = $state(false);
  let testHistory = $state<any[]>([]);

  function loadTemplate() {
    if (selectedTemplate && payloadTemplates[selectedTemplate]) {
      payloadText = JSON.stringify(payloadTemplates[selectedTemplate].template, null, 2);
    }
  }

  async function executeTest() {
    if (!selectedApiClient) {
      alert('Please select an API client');
      return;
    }

    isLoading = true;
    testResponse = null;

    try {
      const response = await fetch('/testing/api-test/execute', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
          method: selectedMethod,
          endpoint: selectedEndpoint,
          payload: payloadText,
          api_client_id: selectedApiClient,
        }),
      });

      const data = await response.json();
      testResponse = data;
      
      // Add to history
      testHistory = [
        {
          timestamp: new Date().toISOString(),
          method: selectedMethod,
          endpoint: selectedEndpoint,
          status: data.response?.status_code || 'Unknown',
          ...data
        },
        ...testHistory.slice(0, 9) // Keep only last 10
      ];
    } catch (error) {
      testResponse = {
        error: 'Failed to execute test',
        details: error.message
      };
    } finally {
      isLoading = false;
    }
  }

  function copyToClipboard(text: string) {
    navigator.clipboard.writeText(text);
  }

  function formatJson(obj: any) {
    return JSON.stringify(obj, null, 2);
  }

  function getMethodColor(method: string) {
    switch (method) {
      case 'GET': return 'bg-green-100 text-green-800';
      case 'POST': return 'bg-blue-100 text-blue-800';
      case 'PUT': return 'bg-yellow-100 text-yellow-800';
      case 'DELETE': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  }

  function getStatusColor(status: number | string) {
    const statusNum = typeof status === 'string' ? parseInt(status) : status;
    if (statusNum >= 200 && statusNum < 300) return 'text-green-600';
    if (statusNum >= 400) return 'text-red-600';
    return 'text-yellow-600';
  }
</script>

<AppLayout title="API Testing Interface">
  <div class="container mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">API Testing Interface</h1>
        <p class="text-muted-foreground">Test your Laravel API endpoints with HMAC authentication</p>
      </div>
      <Button variant="outline" href="/testing/queue-dashboard">
        <ArrowLeft class="mr-2 h-4 w-4" />
        Back to Dashboard
      </Button>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
      <!-- Request Configuration -->
      <div class="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Request Configuration</CardTitle>
            <CardDescription>Configure your API test request</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <!-- API Client Selection -->
            <div class="space-y-2">
              <Label for="api-client">API Client</Label>
              <Select bind:value={selectedApiClient}>
                <SelectTrigger>
                  <SelectValue placeholder="Select an API client" />
                </SelectTrigger>
                <SelectContent>
                  {#each apiClients as client}
                    <SelectItem value={client.id}>
                      {client.name} ({client.key_id})
                    </SelectItem>
                  {/each}
                </SelectContent>
              </Select>
            </div>

            <!-- Method Selection -->
            <div class="space-y-2">
              <Label for="method">HTTP Method</Label>
              <Select bind:value={selectedMethod}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="GET">GET</SelectItem>
                  <SelectItem value="POST">POST</SelectItem>
                  <SelectItem value="PUT">PUT</SelectItem>
                  <SelectItem value="DELETE">DELETE</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Endpoint Input -->
            <div class="space-y-2">
              <Label for="endpoint">Endpoint</Label>
              <Input
                id="endpoint"
                bind:value={selectedEndpoint}
                placeholder="/api/v1/endpoint"
              />
            </div>

            <!-- Execute Button -->
            <Button 
              onclick={executeTest} 
              disabled={isLoading || !selectedApiClient}
              class="w-full"
            >
              {#if isLoading}
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              {:else}
                <Play class="mr-2 h-4 w-4" />
              {/if}
              Execute Test
            </Button>
          </CardContent>
        </Card>

        <!-- Payload Editor -->
        <Card>
          <CardHeader>
            <CardTitle>Request Payload</CardTitle>
            <CardDescription>Configure the JSON payload for your request</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <!-- Template Selection -->
            <div class="space-y-2">
              <Label for="template">Load Template</Label>
              <div class="flex gap-2">
                <Select bind:value={selectedTemplate}>
                  <SelectTrigger>
                    <SelectValue placeholder="Select a template" />
                  </SelectTrigger>
                  <SelectContent>
                    {#each Object.entries(payloadTemplates) as [key, template]}
                      <SelectItem value={key}>{template.name}</SelectItem>
                    {/each}
                  </SelectContent>
                </Select>
                <Button variant="outline" onclick={loadTemplate} disabled={!selectedTemplate}>
                  Load
                </Button>
              </div>
            </div>

            <!-- JSON Editor -->
            <div class="space-y-2">
              <Label for="payload">JSON Payload</Label>
              <Textarea
                id="payload"
                bind:value={payloadText}
                placeholder="Enter JSON payload..."
                class="font-mono text-sm min-h-[200px]"
              />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Response and Documentation -->
      <div class="space-y-6">
        <!-- Test Response -->
        {#if testResponse}
          <Card>
            <CardHeader>
              <CardTitle>Test Response</CardTitle>
              <CardDescription>Response from your API test</CardDescription>
            </CardHeader>
            <CardContent>
              <Tabs defaultValue="response">
                <TabsList class="grid w-full grid-cols-2">
                  <TabsTrigger value="response">Response</TabsTrigger>
                  <TabsTrigger value="request">Request Details</TabsTrigger>
                </TabsList>
                
                <TabsContent value="response" class="space-y-4">
                  {#if testResponse.response}
                    <div class="flex items-center gap-2">
                      <Badge class={getStatusColor(testResponse.response.status_code)}>
                        {testResponse.response.status_code}
                      </Badge>
                      <span class="text-sm text-muted-foreground">
                        {testResponse.response.execution_time}s
                      </span>
                    </div>
                    
                    <div class="space-y-2">
                      <div class="flex items-center justify-between">
                        <h4 class="text-sm font-medium">Response Body</h4>
                        <Button 
                          variant="outline" 
                          size="sm"
                          onclick={() => copyToClipboard(testResponse.response.body)}
                        >
                          <Copy class="h-3 w-3" />
                        </Button>
                      </div>
                      <pre class="bg-muted p-3 rounded text-xs overflow-auto max-h-[300px]">{testResponse.response.body}</pre>
                    </div>
                  {:else if testResponse.error}
                    <div class="text-red-600">
                      <p class="font-medium">Error:</p>
                      <p class="text-sm">{testResponse.error}</p>
                      {#if testResponse.details}
                        <p class="text-xs mt-2">{testResponse.details}</p>
                      {/if}
                    </div>
                  {/if}
                </TabsContent>

                <TabsContent value="request" class="space-y-4">
                  {#if testResponse.request}
                    <div class="space-y-3">
                      <div>
                        <h4 class="text-sm font-medium mb-2">Headers</h4>
                        <pre class="bg-muted p-3 rounded text-xs overflow-auto">{formatJson(testResponse.request.headers)}</pre>
                      </div>
                      
                      {#if testResponse.request.payload}
                        <div>
                          <h4 class="text-sm font-medium mb-2">Payload</h4>
                          <pre class="bg-muted p-3 rounded text-xs overflow-auto">{testResponse.request.payload}</pre>
                        </div>
                      {/if}
                    </div>
                  {/if}
                </TabsContent>
              </Tabs>
            </CardContent>
          </Card>
        {/if}

        <!-- API Endpoints Documentation -->
        <Card>
          <CardHeader>
            <CardTitle>Available Endpoints</CardTitle>
            <CardDescription>Browse and select from available API endpoints</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              {#each endpoints as group}
                <div>
                  <h4 class="font-medium mb-2">{group.group}</h4>
                  <div class="space-y-2">
                    {#each group.endpoints as endpoint}
                      <button
                        class="w-full text-left p-2 rounded border hover:bg-muted transition-colors"
                        onclick={() => {
                          selectedMethod = endpoint.method;
                          selectedEndpoint = endpoint.path;
                        }}
                      >
                        <div class="flex items-center gap-2">
                          <Badge class={getMethodColor(endpoint.method)} variant="outline">
                            {endpoint.method}
                          </Badge>
                          <code class="text-sm">{endpoint.path}</code>
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">{endpoint.description}</p>
                      </button>
                    {/each}
                  </div>
                </div>
              {/each}
            </div>
          </CardContent>
        </Card>

        <!-- Test History -->
        {#if testHistory.length > 0}
          <Card>
            <CardHeader>
              <CardTitle>Test History</CardTitle>
              <CardDescription>Recent API test executions</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-2 max-h-[300px] overflow-auto">
                {#each testHistory as test}
                  <div class="flex items-center justify-between p-2 border rounded">
                    <div class="flex items-center gap-2">
                      <Badge class={getMethodColor(test.method)} variant="outline">
                        {test.method}
                      </Badge>
                      <code class="text-xs">{test.endpoint}</code>
                      <Badge class={getStatusColor(test.status)} variant="outline">
                        {test.status}
                      </Badge>
                    </div>
                    <span class="text-xs text-muted-foreground">
                      {new Date(test.timestamp).toLocaleTimeString()}
                    </span>
                  </div>
                {/each}
              </div>
            </CardContent>
          </Card>
        {/if}
      </div>
    </div>

    <!-- Payload Templates Reference -->
    <Card>
      <CardHeader>
        <CardTitle>Payload Templates</CardTitle>
        <CardDescription>Available JSON payload templates for testing</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="grid gap-4 md:grid-cols-2">
          {#each Object.entries(payloadTemplates) as [key, template]}
            <div class="border rounded-lg p-4">
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium">{template.name}</h4>
                <Button 
                  variant="outline" 
                  size="sm"
                  onclick={() => {
                    selectedTemplate = key;
                    loadTemplate();
                  }}
                >
                  Use Template
                </Button>
              </div>
              <p class="text-sm text-muted-foreground mb-3">{template.description}</p>
              <details class="text-xs">
                <summary class="cursor-pointer text-muted-foreground hover:text-foreground">
                  View Template Structure
                </summary>
                <pre class="bg-muted p-2 rounded mt-2 overflow-auto max-h-[150px]">{formatJson(template.template)}</pre>
              </details>
            </div>
          {/each}
        </div>
      </CardContent>
    </Card>
  </div>
</AppLayout>